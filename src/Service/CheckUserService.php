<?php


namespace App\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class CheckUserService extends AbstractController
{
    function checkCorrespondanceBetween2Passwords($firstPass, $secondPass)
    {
        if ($firstPass != $secondPass)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    function check_password($password)
    {
        $error = null;
        if (strlen($password) < 8)
        {
            $error .= "Password too short!
       ";
        }
        if (strlen($password) > 20)
        {
            $error .= "Password too long!
       ";
        }
        if (!preg_match("#[0-9]+#", $password))
        {
            $error .= "Password must include at least one number!
       ";
        }
        if (!preg_match("#[a-z]+#", $password))
        {
            $error .= "Password must include at least one letter!
       ";
        }
        if (!preg_match("#[A-Z]+#", $password))
        {
            $error .= "Password must include at least one CAPS!
       ";
        }
        if (!preg_match("#\W+#", $password))
        {
            $error .= "Password must include at least one symbol!
       ";
        }

        if ($error != null)
        {
            return $error;
        }
        else
        {
            return true;
        }
    }
}