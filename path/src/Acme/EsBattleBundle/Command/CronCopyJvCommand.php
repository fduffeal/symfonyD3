<?php
namespace Acme\EsBattleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Acme\EsBattleBundle\Entity\Annonce as Annonce;
use Acme\EsBattleBundle\Entity\Tag as Tag;
use Acme\EsBattleBundle\Entity\CronTask;
use Symfony\Component\Security\Acl\Exception\Exception;


class CronCopyJvCommand extends ContainerAwareCommand
{
	private $output;

	protected function configure()
	{
		$this
			->setName('copy:jv')
			->setDescription('copier la derniere page de jv')
			->addArgument('id', InputArgument::REQUIRED, 'quel cron lancer ?')
			->addOption('soft', null, InputOption::VALUE_NONE, 'Si définie, pas de sauvegarde en base')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$cronId = $input->getArgument('id');
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');

		$output->writeln('CRON ID ' . $cronId);
		/**
		 * @var \Acme\EsBattleBundle\Entity\CronTask $crontask
		 */
		$crontask = $em->getRepository('AcmeEsBattleBundle:CronTask')->find($cronId);

		if ($crontask->getLocked() === true) {


			$lockTimeMax = strtotime('-10 minutes', time());

			$timeLastRun = $crontask->getLastrun()->getTimestamp();

			if($lockTimeMax < $timeLastRun){
				$output->writeln('CRON ID ' . $cronId . ' LOCKED ---- bye bye ---');
				return;
			}

			$output->writeln('CRON ID ' . $cronId . ' LOCKED ---- max lock time raised, try to execute ---');

		}

		$crontask->setLocked(true);
		$em->persist($crontask);
		$em->flush();

		$url = $crontask->getCommands();
		$output->writeln('Copie de ' . $url);
		$previousLastIdSave = intval($crontask->getOutput());
		$output->writeln('Dernier post sauvegardé : ' . $previousLastIdSave);

		$soft = false;
		if ($input->getOption('soft')) {
			$soft = true;
			$output->writeln('--MODE SOFT--');
		}

		try {

			$jeuxvideo = $this->getContainer()->get('acme_es_battle.jeuxvideo');

			$lastPage = $jeuxvideo->getLastPage($url);

			$output->writeln('Derniere page ' . $lastPage);

			$lastIdSave = $this->_copyJeuxVideoAction($lastPage, $soft, $output, $previousLastIdSave);

			if ($lastIdSave !== null) {
				$crontask->setOutput($lastIdSave);
				$crontask->setLastrun(new \DateTime());
			}

			$crontask->setLocked(false);
			$em->persist($crontask);
			$em->flush();

		}catch (\Exception $e){
			$output->writeln('Exception ' . $e->getMessage());
			$output->writeln($e->getTrace());
		}

		$output->writeln('--END --');
	}

	private function _copyJeuxVideoAction($url,$soft,$output,$previousLastIdSave){

		$em = $this->getContainer()->get('doctrine.orm.entity_manager');
		/**
		 * Acme\EsBattleBundle\JeuxVideo $jeuxvideo
		 */
		$jeuxvideo = $this->getContainer()->get('acme_es_battle.jeuxvideo');
		$bungie = $this->getContainer()->get('acme_es_battle.bungie');

		$pagePost = $jeuxvideo->getPage($url);

		$aPost =[];
		foreach($pagePost as $post) {
			if ($post === null) {
				continue;
			}
			$aPost[] = $post;
		}

		$nbPost = sizeof($aPost);
		$output->writeln('--'.$nbPost.' POST--');
		/**
		 * check previous page
		 */
		if($nbPost === 0){
			$aUrl = explode('-',$url);

			$aUrl[3] = intval($aUrl[3])-1;

			$url = implode('-',$aUrl);

			$pagePost = $jeuxvideo->getPage($url);

			$aPost =[];
			foreach($pagePost as $post) {
				if ($post === null) {
					continue;
				}
				$aPost[] = $post;
			}

			$nbPost = sizeof($aPost);
			$output->writeln('--'.$nbPost.' POST PREVIOUS PAGE--');
		}

//        echo 'NB POST :'. sizeof($aPost).'<br/>';

		$plateformId = $jeuxvideo->plateform;//PS4
		/**
		 * @var \Acme\EsBattleBundle\Entity\Plateform $plaform
		 */
		$plaform = $em->getRepository('AcmeEsBattleBundle:Plateform')
			->findOneBy(
				array('id' => $plateformId)
			);

		/**
		 * @var \Acme\EsBattleBundle\Entity\Game $game
		 */
		$game = $em->getRepository('AcmeEsBattleBundle:Game')
			->findOneBy(
				array('id' => $bungie->getDestinyGameId())
			);

//        var_dump($pagePost[0]);die();

		$lastPostId = null;
		$aAnnonce = [];
		foreach($aPost as $post){
			if($post === null){
				continue;
			}

			if($post->id <= $previousLastIdSave){
				$output->writeln($post->id." inférieur au dernier post sauvegardé");
				continue;
			}
//            var_dump($post->gamerTag);die();
			$displayName = $post->gamerTag;
			$membershipType = $plaform->getBungiePlateformId();

			$characters = $bungie->getCharacters($membershipType,$displayName);

			if($characters === null){
//                echo $displayName.' not found<br/>';
				continue;
			}

			$characters = $bungie->sortCharacters($characters);

			$userGameToSave = null;

			foreach($characters as $character){
				if($character['class'] ===  $post->class){
//                    echo 'i m '. $post->class.'<br/>';
					$userGameToSave = $character;
					break;
				}
			}

			if($userGameToSave === null){
				$userGameToSave = $characters[0];
			}

			$userGame = $bungie->saveGameUserInfo($userGameToSave,null,$plaform,$game);


			/**
			 * @var \Acme\EsBattleBundle\Entity\Annonce $annonce
			 */
			$annonce = new Annonce();
			$annonce->setDescription($post->message);
			$annonce->setAuthor($userGame);
			$annonce->setPlateform($plaform);
			$annonce->setGame($game);


			$aTags = preg_split("/[\s,]+/",$post->tags);


			foreach($aTags as $key){
				$selectedTag = $em->getRepository('AcmeEsBattleBundle:Tag')
					->findOneBy(array('nom' => $key));

				$key = trim($key);

				if($selectedTag === null && $key !== ""){
					$selectedTag = new Tag();
					$selectedTag->setNom($key);
					$selectedTag->setPoids(0);

					if(!$soft){
						$em->persist($selectedTag);
					}
				}

				if($selectedTag !== null){
					$annonce->addTag($selectedTag);
				}
			}

			if(!$soft){
				$em->persist($annonce);
			}


			$date = new \DateTime();
			$date->setTimestamp($post->date);

			$output->writeln('POST '.$post->id);
			$lastPostId = $post->id;
			$output->writeln('DATE: '.$date->format('c'));
			$annonce->setCreated($date);

			$aAnnonce[] = $annonce->_toArray();
		}

		if(!$soft){
			$em->flush();
		}

		$output->writeln(sizeof($aAnnonce).' annonces créées');

		return $lastPostId;
	}
}