<?php

/**
 *
 *
 */

namespace App\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls;
use App\Model\Repository\ZamestnanecRepository;


class ZamestnanecForm
{

    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;




    public function __construct(ZamestnanecRepository $zamestnanecRepository)
    {
        //parent::__construct();
        $this->zamestnanecRepository = $zamestnanecRepository;
    }




    public function create()
    {
        $form = new Nette\Application\UI\Form;


        $form->getElementPrototype()->class('ajax');

        $form->addGroup('');
        $form->addText('zamestnanecId', 'Id')->setAttribute('readonly','readonly');
        $form->addText('jmeno', 'Jméno Příjmení')->setRequired('Zadejte prosím jméno');
        $form->addText('pohlavi', 'pohlaví');
        $form->addText('narozeni', 'narození');
        $form->addText('zapoctenaPraxe', 'započtená praxe');
        $form->addText('nastup', 'nástup');
        $form->addText('vzdelani', 'vzdělání');
        $form->addText('invalidita', 'invalidita');
        $form->addText('osobniCislo', 'osobní číslo');
      //  $form->addText('floor', 'Podlaží')->setRequired('Zadejte unikátní číslo místnosti');//->setAttribute('readonly','readonly');


      //  $form->addSelect('buildingId', 'Budova', $this->buildingRepository->getBuildingNames())->setRequired('Vyberte budovu');
      //  $form->addText('zamestnanecNumber', 'Označení místnosti')->setRequired('Zadejte označení místnosti');




        $form->addSubmit('submit', 'Uložit informace o zaměstnanecovi');
        $form->addSubmit('delete', 'Odstranit zaměstnaneca')->setHtmlAttribute("onclick", 'return confirm("Opravdu odstranit?")');
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
      $zamestnanecId = $values->zamestnanecId;

      $data = $values;

      if ($zamestnanecId) {
          $zamestnanec = $this->zamestnanecRepository->getZamestnanec($zamestnanecId);
          $zamestnanec->update($data);
      }
      else {
          $zamestnanecId = $this->zamestnanecRepository->addZamestnanec($data);
          }

    }

    private function delete($form, $values)
    {
      try{
      $this->zamestnanecRepository->deleteZamestnanec($values->zamestnanecId);
      }
      catch(Nette\Database\ForeignKeyConstraintViolationException $e){
        $form->addError('Zaměstnaneca není možné odstranit. Pravděpodobně má přiřazené objednávky nebo faktury.');
      }
        //  $zamestnanecId = $this->zamestnanecRepository->addZamestnanec($data);
         // $zamestnanec = $this->zamestnanecRepository->getZamestnanec($zamestnanecId);
          //$this->flashMessage('Bylo zaevidováno nové zařízení: '.$values->name, 'alert-success');

    }

}
