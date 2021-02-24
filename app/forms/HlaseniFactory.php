<?php

/**
 *
 *
 */

namespace App\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls;
use App\Model\Repository\HlaseniRepository;


class HlaseniForm
{

    /** @var HlaseniRepository */
    private $hlaseniRepository;




    public function __construct(HlaseniRepository $hlaseniRepository)
    {
        //parent::__construct();
        $this->hlaseniRepository = $hlaseniRepository;
    }




    public function create($zamestnanecId)
    {
        $form = new Nette\Application\UI\Form;


        $form->getElementPrototype()->class('ajax');
        $pozice = [
            'Pracovníci v přímé péči' =>[
                '1.1.'=>	'Sociální pracovníci',
                '1.2.' =>	'Pracovník v sociálních službách',
                '1.2.1.' =>	'přímá obslužná péče',
                '1.2.2.' =>	'základní výchovná nepedagogická činnost',
                '1.2.3.' =>	'pečovatelská činnost',
                '1.2.4.' =>	'činnosti pod dohledem sociálního pracovníka',
                '1.3.' =>	"Zdravotničtí pracovníci",
                '1.4.' =>	'Pedagogičtí pracovníci',
                '1.5.' =>	'Manželští a rodinní poradci',
                '1.6.' =>	'Další odborní pracovníci, kteří přímo poskytují sociální služby'
            ],
            'Ostatní pracovníci' =>[
                '2.1.' =>	'Vedoucí pracovníci',
                '2.2.' =>	'Administrativní pracovníci',
                '2.3.' =>	'Ostatní pracovníci'
            ]
        ];

        $form->addGroup('');
        $form->addText('zamestnanecId', '')->setDefaultValue($zamestnanecId);
        $form->addSelect('typPomeru', 'Typ pracovního poměru', ['HPP', 'DPP', 'DPČ']);
        $form->addInteger('velikostPomeru', 'Velikost pracovního poměru (hodiny, nebo úvazek)');
        $form->addSelect('typPozice', 'Typ pracovní pozice', $pozice);
        $form->addText('datum', 'Datum nástupu/ změny')->setHtmlType('date');
        $form->addSelect('typUdalosti', 'Typ události', ['Ukončení','Změna','Nástup']);
        $form->addText('it', 'IT (odkaz na úkolovník)');
        $form->addText('vizitky', 'Vizitky');
        $form->addCheckbox('BOZP', 'BOZP');
        $form->addCheckbox('PO', 'PO');
        $form->addCheckbox('ridici', 'Školení řidičů');
        $form->addText('univerzalniKlic', 'Univerzální klíč');
        $form->addTextArea('poznamka', 'Poznámky');
      //  $form->addText('floor', 'Podlaží')->setRequired('Zadejte unikátní číslo místnosti');//->setAttribute('readonly','readonly');


      //  $form->addSelect('buildingId', 'Budova', $this->buildingRepository->getBuildingNames())->setRequired('Vyberte budovu');
      //  $form->addText('hlaseniNumber', 'Označení místnosti')->setRequired('Zadejte označení místnosti');




        $form->addSubmit('submit', 'Uložit hlášení');
        $form->addSubmit('delete', 'Odstranit zaměstnance')->setHtmlAttribute("onclick", 'return confirm("Opravdu odstranit?")');
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
      $hlaseniId = null;//$values->hlaseniId;

      $data = $values;

      if ($hlaseniId) {
          $hlaseni = $this->hlaseniRepository->getHlaseni($hlaseniId);
          $hlaseni->update($data);
      }
      else {
          $hlaseniId = $this->hlaseniRepository->addHlaseni($data);
          }

    }

    private function delete($form, $values)
    {
      try{
      $this->hlaseniRepository->deleteHlaseni($values->hlaseniId);
      }
      catch(Nette\Database\ForeignKeyConstraintViolationException $e){
        $form->addError('Zaměstnaneca není možné odstranit. Pravděpodobně má přiřazené objednávky nebo faktury.');
      }
        //  $hlaseniId = $this->hlaseniRepository->addHlaseni($data);
         // $hlaseni = $this->hlaseniRepository->getHlaseni($hlaseniId);
          //$this->flashMessage('Bylo zaevidováno nové zařízení: '.$values->name, 'alert-success');

    }

}
