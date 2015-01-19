<?php
/**
 * Created by PhpStorm.
 * User: francis.duffeal
 * Date: 19/01/2015
 * Time: 11:57
 */

namespace Acme\EsBattleBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * CronTask
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CronTask
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @ORM\Column(type="string")
	 */
	private $commands;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $lastrun;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $output;

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getCommands()
	{
		return $this->commands;
	}

	public function setCommands($commands)
	{
		$this->commands = $commands;
		return $this;
	}

	public function getInterval()
	{
		return $this->interval;
	}

	public function setInterval($interval)
	{
		$this->interval = $interval;
		return $this;
	}

    /**
     * Set lastrun
     *
     * @param \DateTime $lastrun
     * @return CronTask
     */
    public function setLastrun($lastrun)
    {
        $this->lastrun = $lastrun;

        return $this;
    }

    /**
     * Get lastrun
     *
     * @return \DateTime 
     */
    public function getLastrun()
    {
        return $this->lastrun;
    }

    /**
     * Set output
     *
     * @param string $output
     * @return CronTask
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get output
     *
     * @return string 
     */
    public function getOutput()
    {
        return $this->output;
    }
}
