<?php

/**
 *
 *
 */

namespace App\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls;
use App\Model\Repository\WRRepository;
use Nette\Security\User;


class AWRForm
{

    /** @var WRRepository */
    private $wRRepository;
    /** @var User */
    private $user;




    public function __construct(WRRepository $wRRepository, Nette\Security\User $user)
    {
        //parent::__construct();
        $this->wRRepository = $wRRepository;
        $this->user = $user;
    }




    public function create()
    {
        $form = new Nette\Application\UI\Form;


        $form->getElementPrototype()->class('ajax');

        $form->addGroup('');
        $form->addText('pomerId', 'Id')->setAttribute('readonly','readonly');
        $form->addText('nastup', 'nástup')->setType('date');
        $form->addText('vystup', 'výstup')->setType('date');
        $form->addText('mzda', 'mzda');
        $form->addText('priplatekZaRizeni', 'příplatek za řízení');
        $form->addText('osobniHodnoceni', 'osobní hodnocení');
        $form->addText('primaPece', 'přímá péče');
        $form->addText('prepoctenyUvazek', 'přepočtený úvazek');
        $form->addText('souladSS', 'soulad s SS');
        $form->addText('zamestnanecId', 'Zaměstnanec')->setAttribute('readonly','readonly');
      //  $form->addText('fk_invoice', 'Faktura')->setAttribute('readonly','readonly');
        //$form->addText('zamestnanecName', 'Zaměstnanec')->setDisabled();//->setAttribute('readonly','readonly');


      //  $form->addSelect('buildingId', 'Budova', $this->buildingRepository->getBuildingNames())->setRequired('Vyberte budovu');
      //  $form->addText('orderNumber', 'Označení místnosti')->setRequired('Zadejte označení místnosti');




        // $form->addSubmit('submit', 'Uložit objednávku');
        // $form->addSubmit('delete', 'Odstranit objednávku')->setHtmlAttribute("onclick", 'return confirm("Opravdu odstranit?")');
        $form->onSuccess[] = [$this, 'processForm'];


        // setup form rendering
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';


        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');
        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            }
            elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            }
            elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }


        return $form;
    }

    public function processForm($form, $values)
    {
      if ($form['submit']->isSubmittedBy())
        $this->updateCreate($form, $values);
      else if($form['delete']->isSubmittedBy())
        $this->delete($form, $values);

    }

    private function updateCreate($form, $values)
    {
      $pomerId = $values->pomerId;
      $data = $values;

      if ($pomerId) {
        $wR = $this->wRRepository->getWR($pomerId);
        if($values['price']!= $wR->price){
          $data['price_timestamp']=date('Y-m-d H:i:s',time());
          $data['price_timestamp_created_by']=$this->user->getId();
        }
        if(!$pomerId = $this->wRRepository->updateWR($data))
            $form->addError('Vyfakturovanou objednávku není možné měnit.');
      }
      else {
          //$data['created']=date('Y-m-d H:i:s',time());
          $data['created_by']=$this->user->getId();
          if($values['price']!= 0){
            $data['price_timestamp']=date('Y-m-d H:i:s',time());
            $data['price_timestamp_created_by']=$this->user->getId();
          }
          $pomerId = $this->wRRepository->addWR($data);
          }

    }

    private function delete($form, $values)
    {
      try{
        if(!$this->wRRepository->deleteWR($values->pomerId))
            $form->addError('Vyfakturovanou objednávku není možné odstranit.');
      }
      catch(Nette\Database\ForeignKeyConstraintViolationException $e){
        $form->addError('Vyfakturovanou objednávku není možné odstranit.');
      }

    }

}
