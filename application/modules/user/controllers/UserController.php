<?php
namespace modules\user\controllers;

use core\Registry;
use core\View;
use \Exception;
use modules\user\model;
use core\Validator;
use core\classTables\Users;


class UserController
{
    public function registrationAction()
    {
        $module=Registry::getValue('module');
        $view = new View($module, 'registration.php');
        $view->assign('title', 'New user');
        if(isset($_POST['username'], $_POST['password_1'], $_POST['password_2'],
           $_POST['email'], $_POST['date_of_birthday']) )
        {
            $username=$_POST['username'];
            $password_1= $_POST['password_1'];
            $password_2=$_POST['password_2'];
            $email=$_POST['email'];
            $date_of_birthday=$_POST['date_of_birthday'];

            $usernameValid=new Validator($username);
            $password1Valid=new Validator($password_1);
            $password2Valid=new Validator($password_2);
            $emailValid=new Validator($email);
            $dateOfBirthdayValid=new Validator($date_of_birthday);

            $usernameValid->isEmpty('Заполните поле !');
            $usernameValid->hasFormat('/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/', 'Поле не валидно!');
            $usernameValid->isUsernameTaken("Такой логин уже существует !");
            if($usernameValid->isValid()){

            }else{
                $message='';
                foreach($usernameValid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('username_message', $message);
            }

            $password1Valid->isEmpty('Заполните поле !');
            $password1Valid->lengthBetween(16, 8, "Максимальная длинна поля 16, минимальная 8!");
            $password1Valid->hasFormat('/([a-zA-Z]+[0-9]+)|([0-9]+[a-zA-Z]+)/','Поле должно содержать по крайней мере 1 символ и 1 цифру !');
            if($password1Valid->isValid()){

            }else{
                $message='';
                foreach($password1Valid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('password_1_message', $message);
            }

            $password2Valid->isEmpty('Заполните поле !');
            $password2Valid->isEqual($password_1,'',"Пароли не совпадают !");
            if($password2Valid->isValid()){

            }else{
                $message='';
                foreach($password2Valid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('password_2_message', $message);
            }

            $emailValid->isEmpty('Заполните поле !');
            $emailValid->hasFormat('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/','Неверный email !');
            $emailValid->isEmailTaken('Этот email уже занят !');
            if($emailValid->isValid()){

            }else{
                $message='';
                foreach($emailValid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('email_message',$message);
            }

            $dateOfBirthdayValid->isEmpty('Заполните поле !');
            $dateOfBirthdayValid->hasFormat('/^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/','Дата не соответствует шаблону !');
            if($dateOfBirthdayValid->isValid()){

            }else{
                $message='';
                foreach( $dateOfBirthdayValid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('date_of_birthday_message', $message);
            }

            if(count($usernameValid->errorStack)==0 &&
               count($password1Valid->errorStack)==0 &&
               count($password2Valid->errorStack)==0 &&
               count($emailValid->errorStack)==0 &&
               count($dateOfBirthdayValid->errorStack)==0)
            {
                $insertUser=new Users();
                $insertUser->insert(['username'=>$username, 'email'=>$email, 'password'=>$password_1,
                'date_of_birth'=>$date_of_birthday]);
                $view->assign('global_message', "Вы успешно зарегистрировались !");
            }

        }

            try {
                $view->addIntoTemplate();
                $view->display();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
    }

    public function  signinAction(){
        $module = Registry::getValue('module');
        $view = new View($module, 'signin.php');
        $view->assign('title','Вход');

        if(isset($_POST['username'], $_POST['password_1']))
        {
            $username=$_POST['username'];
            $password_1= $_POST['password_1'];

            $usernameValid=new Validator($username);
            $password1Valid=new Validator($password_1);

            $usernameValid->isEmpty('Заполните поле !');
            $usernameValid->hasFormat('/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/', 'Поле не валидно!');
            if($usernameValid->isValid()){

            }else{
                $message='';
                foreach($usernameValid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('username_message', $message);
            }

            $password1Valid->isEmpty('Заполните поле !');
            $password1Valid->lengthBetween(16, 8, "Максимальная длинна поля 16, минимальная 8!");
            $password1Valid->hasFormat('/([a-zA-Z]+[0-9]+)|([0-9]+[a-zA-Z]+)/','Поле должно содержать по крайней мере 1 символ и 1 цифру !');
            if($password1Valid->isValid()){

            }else{
                $message='';
                foreach($password1Valid->getErrors() as $value)
                {
                    $message.=$value."</br>";
                }
                $view->assign('password_1_message', $message);
            }

            if(count($usernameValid->errorStack)==0 &&
               count($password1Valid->errorStack)==0)
            {
                $user=new model\User();
                $user->login=$username;
                $user->password=$password_1;
                $userDAO=new model\UserDAO();
                if(count($userDAO->isUserExists($user))==0)
                {
                    $view->assign('global_message', "Вы еще не зарегистрированы !");
                }else
                {
                    ob_start();
                    session_start();
                    $_SESSION['username']=$username;
                    header('Location: /main/index/index');
                    ob_end_flush();
                }

            }

        }


        try
        {
            $view -> addIntoTemplate();
            $view -> display();
        } catch (Exception $e)
        {
            echo $e -> getMessage();
        }

    }

    public function profileAction()
    {
        $User = new model\User();
        $User->profile($_SESSION['user_id']);
        $module = Registry::getValue('module');
        $v = new View($module, 'profile.php');
        foreach ($User as $property => $value) {
            $v->assign($property, $value);
        }
        try {
            $v -> addIntoTemplate();
            $v -> display();
        } catch (Exception $e) {
            echo $e -> getMessage();
        }
    }
}
