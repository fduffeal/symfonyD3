<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Acme\EsBattleBundle\Entity\User as User;

use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function indexAction($email,$password)
    {

        $user = $this->getDoctrine()
            ->getRepository('AcmeEsBattleBundle:User')
            ->findOneBy(
                array('email' => $email)
            );

	    if($user->isPasswordOk($password)){

            $user->setApikey($user->createApiKey());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $aUser = array(
                'username' => $user->getUsername(),
                'token' => $user->getApikey()
            );

            $json = json_encode($aUser);

		    return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	    } else {
		    return new Response(null, 404, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	    }

    }

	/**
	 * @param $email
	 * @param $password
	 * @param $username
	 * @return Response
	 *
	 * @todo checker email et username
	 */
	public function registerAction($email,$password,$username)
	{

        $contenuMail = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Template mailing Alsacreations</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
    /* Fonts and Content */
    body, td { font-family: \'Helvetica Neue\', Arial, Helvetica, Geneva, sans-serif; font-size:14px; }
    body { background-color: #2A374E; margin: 0; padding: 0; -webkit-text-size-adjust:none; -ms-text-size-adjust:none; }
    h2{ padding-top:12px; /* ne fonctionnera pas sous Outlook 2007+ */color:#0E7693; font-size:22px; }

    </style>

</head>
<body style="margin:0px; padding:0px; -webkit-text-size-adjust:none;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:rgb(42, 55, 78)" >
        <tbody>
            <tr>
                <td align="center" bgcolor="#2A374E">
                    <table  cellpadding="0" cellspacing="0" border="0">
                        <tbody>
                            <tr>
                                <td class="w640"  width="640" height="10"></td>
                            </tr>

                            <tr>
                                <td align="center" class="w640"  width="640" height="20"> <a style="color:#ffffff; font-size:12px;" href="#"><span style="color:#ffffff; font-size:12px;">Voir le contenu de ce mail en ligne</span></a> </td>
                            </tr>
                            <tr>
                                <td class="w640"  width="640" height="10"></td>
                            </tr>


                            <!-- entete -->
                            <tr class="pagetoplogo">
                                <td class="w640"  width="640">
                                    <table  class="w640"  width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#F2F0F0">
                                        <tbody>
                                            <tr>
                                                <td class="w30"  width="30"></td>
                                                <td  class="w580"  width="580" valign="middle" align="left">
                                                    <div class="pagetoplogo-content">
                                                        <img class="w580" style="text-decoration: none; display: block; color:#476688; font-size:30px;" src="logo.png" alt="Mon Logo" width="482" height="108"/>
                                                    </div>
                                                </td>
                                                <td class="w30"  width="30"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- separateur horizontal -->
                            <tr>
                                <td  class="w640"  width="640" height="1" bgcolor="#d7d6d6"></td>
                            </tr>

                             <!-- contenu -->
                            <tr class="content">
                                <td class="w640" class="w640"  width="640" bgcolor="#ffffff">
                                    <table class="w640"  width="640" cellpadding="0" cellspacing="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td  class="w30"  width="30"></td>
                                                <td  class="w580"  width="580">
                                                    <!-- une zone de contenu -->
                                                    <table class="w580"  width="580" cellpadding="0" cellspacing="0" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td class="w580"  width="580">
                                                                    <h2 style="color:#0E7693; font-size:22px; padding-top:12px;">
                                                                        Welcome '.$username.' !  </h2>

                                                                    <div align="left" class="article-content">
                                                                        <p> On peut mettre des paragraphes</p>
                                                                        <p>
                                                                            Chocolate bar marshmallow dessert wafer topping sugar plum pudding. Dragée dessert gummi bears brownie powder soufflé tootsie roll <a href="">cotton candy tiramisu. </a>
                                                                        </p>
                                                                        <p>
                                                                            Lemon drops icing croissant. Sweet cotton candy macaroon gingerbread chocolate bar wafer cotton candy powder sugar plum.
                                                                        </p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w580"  width="580" height="1" bgcolor="#c7c5c5"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <!-- fin zone -->

                                                    <!-- une autre zone de contenu -->
                                                    <table class="w580"  width="580" cellspacing="0" cellpadding="0" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="3">
                                                                   <h2 style="color:#0E7693; font-size:22px; padding-top:12px;">
                                                                        Une zone avec deux colonnes </h2>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w275"  width="275" valign="top">
                                                                    <div align="left" class="article-content">
                                                                        <p> Avec des listes ça marche aussi </p>
                                                                        <ul>
                                                                            <li> des kiwis</li>
                                                                            <li> des chatons</li>
                                                                            <li> des citrons </li>
                                                                        </ul>
                                                                    </div>
                                                                </td>
                                                                <td class="w30"  width="30" class="w30"></td>
                                                                <td class="w275"  width="275" valign="top">
                                                                    <div align="left" class="article-content">
                                                                        <p> Lemon drops icing croissant. Sweet cotton candy macaroon gingerbread chocolate bar wafer cotton candy powder sugar plum. </p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3" class="w580" height="1" bgcolor="#c7c5c5"></td>
                                                            </tr>

                                                        </tbody>
                                                    </table>

                                                    <table class="w580"  width="580" cellpadding="0" cellspacing="0" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="5">
                                                                   <h2 style="color:#0E7693; font-size:22px; padding-top:12px;">
                                                                       Mise en page 3 colonnes </h2>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="w180"  width="180" valign="top">
                                                                    <div align="left" class="article-content">
                                                                        <p>Des kiwis et des chatons !! Des kiwis et des chatons !!</p>
                                                                    </div>
                                                                </td>

                                                                <td class="w20"  width="20"></td>
                                                                <td class="w180"  width="180" valign="top">
                                                                    <div align="left" class="article-content">
                                                                        <p>Des kiwis et des chatons !! Des kiwis et des chatons !!</p>
                                                                    </div>
                                                                </td>

                                                                <td class="w20"  width="20"></td>
                                                                <td class="w180"  width="180" valign="top">
                                                                    <div align="left" class="article-content">
                                                                        <p><img class="w180" width="180" src="kitten.jpg" alt="un chaton"/></p>
                                                                    </div>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td colspan="5" class="w580"  width="580" height="1" bgcolor="#c7c5c5"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td class="w30" class="w30"  width="30"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!--  separateur horizontal de 15px de  haut-->
                            <tr>
                                <td class="w640"  width="640" height="15" bgcolor="#ffffff"></td>
                            </tr>

                            <!-- pied de page -->
                            <tr class="pagebottom">
                                <td class="w640"  width="640">
                                    <table class="w640"  width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#c7c7c7">
                                        <tbody>
                                            <tr>
                                                <td colspan="5" height="10"></td>
                                            </tr>
                                            <tr>
                                                <td class="w30"  width="30"></td>
                                                <td class="w580"  width="580" valign="top">
                                                    <p align="right" class="pagebottom-content-left">
                                                        <a style="color:#255D5C;" href="www.esbattle.com"><span style="color:#255D5C;">Let\'s go to Esbattle.com</span></a>
                                                    </p>
                                                </td>

                                                <td class="w30"  width="30"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" height="10"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="w640"  width="640" height="60"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>';

        $headers = 'From: no-reply@esbattle.com' . "\r\n" .
            'Reply-To: no-reply@esbattle.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        $mailed = mail("francis.duffeal@gmail.com", "Welcome to Esbattle.com", $contenuMail, $headers);

        var_dump($mailed);die();

		$user = new User;

		$user->setEmail($email);
		$user->setUsername($username);
		$user->setPassword($user->makePassword($password));

		$user->setApikey($user->createApiKey());

		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

        $aUser = array(
            'username' => $user->getUsername(),
            'token' => $user->getApikey()
        );

        $json = json_encode($aUser);

        return new Response($json, 201, array('Access-Control-Allow-Origin' => 'http://localhost:8000', 'Content-Type' => 'application/json'));
	}
}
