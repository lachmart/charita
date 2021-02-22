<?php

/**
 *
 *
 */

namespace App\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls;
use App\Model\Repository\MzdovyComRepository;
use App\Model\Repository\WRRepository;


class MzdovyComForm
{

    /** @var MzdovyComRepository */
    private $mzdovyComRepository;
    /** @var WRRepository */
    private $wRRepository;




    public function __construct(MzdovyComRepository $mzdovyComRepository,WRRepository $wRRepository)
    {
        //parent::__construct();
        $this->mzdovyComRepository = $mzdovyComRepository;
        $this->wRRepository = $wRRepository;
    }




    public function create($mzdovyComId)
    {
        $form = new Nette\Application\UI\Form;


        $form->getElementPrototype()->class('ajax');

        $form->addGroup('');
        $form->addText('mzdovyComId', 'Id')->setAttribute('readonly','readonly');
        $form->addText('zamestnanecName', 'Jméno zaměstnance')->setAttribute('readonly','readonly');
        $form->addText('periodId', 'měsíc')->setAttribute('readonly','readonly');
        $form->addText('FK_pracovniPomer', 'p. poměr')->setAttribute('readonly','readonly');
        $form->addText('hrubaMzda', 'hrubá mzda')->setDefaultValue($mzdovyComId)->setAttribute('readonly','readonly');
        $form->addText('socialniPojistneZamestnavatel', 'sp z-tel')->setDisabled();
        $form->addText('zdravotniPojistneZamestnavatel', 'zp z-tel')->setDisabled();
        $form->addText('mzdoveNaklady', 'mzdové náklady')->setDisabled();
        /*$form->addText('mesicniOdmeny', 'měsíční odměny')->setDisabled();
        $form->addText('premie', 'prémie')->setDisabled();
        $form->addText('trinactyPlat', 'třináctý plat')->setDisabled();
        $form->addText('odmenaZaDelsiObdobi', 'odměna za delší období')->setDisabled();
        $form->addText('odpracovaneHodinyVcetnePrescasu', 'odpracovaneHodinyVcetnePrescasu')->setDisabled();
        $form->addText('zTohoPrescas', 'z toho přesčas')->setDisabled();
        $form->addText('dovolena', 'dovolená')->setDisabled();
        $form->addText('placeneVolno', 'placené volno')->setDisabled();
        $form->addText('nemoc', 'nemoc')->setDisabled();
        $form->addText('celkemHodin', 'celkem hodin')->setDisabled();*/


      //  $form->addSubmit('submit', 'Přepočítat fakturu');
      //  $form->addSubmit('delete', 'Odstranit fakturu');
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
      $mzdovyComId = $values->mzdovyComId;


      $data = $values;
    //  $data['id'] = 1;
      $data['total']=$this->wRRepository->getTotalMzdovyCom($mzdovyComId);



      if ($mzdovyComId) {
          $mzdovyCom = $this->mzdovyComRepository->getMzdovyCom($mzdovyComId);
          $mzdovyCom->update($data);
      }
      else {
          $data['created']=date('Y-m-d H:i:s',time());
          $mzdovyComId = $this->mzdovyComRepository->addMzdovyCom($data);
          }

    }

    private function delete($form, $values)
    {
      try{
      $this->mzdovyComRepository->deleteMzdovyCom($values->mzdovyComId);
      }
      catch(Nette\Database\ForeignKeyConstraintViolationException $e){
        $form->addError('Fakturu není možné odstranit. Pravděpodobně má přiřazené objednávky.');
      }
        //  $mzdovyComId = $this->mzdovyComRepository->addMzdovyCom($data);
         // $mzdovyCom = $this->mzdovyComRepository->getMzdovyCom($mzdovyComId);
          //$this->flashMessage('Bylo zaevidováno nové zařízení: '.$values->name, 'alert-success');

    }

}
