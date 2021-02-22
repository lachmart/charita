<?php

namespace FrontModule;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\SmartObject;

class SignPresenter extends Nette\Application\UI\Presenter
{

    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function actionDefault()
    {
        if($this->user->isLoggedIn()) {
            $this->redirect('Homepage:default');
        } else {
            $this->redirect('Sign:in');
        }
    }

    public function actionOut()
    {
        $this->user->logout();
        $this->flashMessage('Byli jste úspěšně odhlášeni.', 'alert alert-info');
        $this->redirect('Sign:in');
    }


	protected function createComponentSignInForm()
	{
        $form = new Form;

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte heslo');

        $form->addCheckbox('remember', 'Zůstat přihlášený');

        $form->addSubmit('send', 'Přihlásit se');

        $form->addProtection('Vypršela platnost tohoto formuláře, prosím přihlašte se znovu.');

        $form->onSuccess[] = array($this, 'signInFormSucceeded');

        return $form;
	}



    public function signInFormSucceeded(Form $form, $values)
    {
        if ($values->remember) {
            $this->user->setExpiration('14 days', FALSE);
        } else {
            $this->user->setExpiration('2 hours', TRUE);
        }

        try {
            $this->user->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $this->flashMessage('Nesprávné přihlašovací jméno nebo heslo.', 'alert alert-danger');
        }

        $this->redirect('Zamestnanec:default');
    }


}
