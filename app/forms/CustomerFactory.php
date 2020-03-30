<?php

/**
 *
 *
 */

namespace App\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls;
use App\Model\Repository\CustomerRepository;


class CustomerForm
{

    /** @var CustomerRepository */
    private $customerRepository;




    public function __construct(CustomerRepository $customerRepository)
    {
        //parent::__construct();
        $this->customerRepository = $customerRepository;
    }




    public function create()
    {
        $form = new Nette\Application\UI\Form;


        $form->getElementPrototype()->class('ajax');

        $form->addGroup('');
        $form->addText('customerId', 'Id')->setAttribute('readonly','readonly');
        $form->addText('billingFullName', 'Jméno, Příjmení')->setRequired('Zadejte prosím jméno');
        $form->addText('billingStreet', 'Ulice, číslo')->setRequired('Zadejte ulici (je potřeba kvůli faktuře)');
        $form->addText('billingCity', 'Město')->setRequired('Zadejte město (je taky potřeba kvůli faktuře)');
        $form->addText('billingZip', 'Psč')->setRequired('Zadejte psč (k adrese se hodí i psč ;))');
        $form->addText('email', 'Email');
        $form->addText('phone', 'Telefon');
        $form->addText('remark', 'Poznámka');
      //  $form->addText('floor', 'Podlaží')->setRequired('Zadejte unikátní číslo místnosti');//->setAttribute('readonly','readonly');


      //  $form->addSelect('buildingId', 'Budova', $this->buildingRepository->getBuildingNames())->setRequired('Vyberte budovu');
      //  $form->addText('customerNumber', 'Označení místnosti')->setRequired('Zadejte označení místnosti');




        $form->addSubmit('submit', 'Uložit informace o zákazníkovi');
        $form->addSubmit('delete', 'Odstranit zákazníka')->setHtmlAttribute("onclick", 'return confirm("Opravdu odstranit?")');
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
      $customerId = $values->customerId;

      $data = $values;

      if ($customerId) {
          $customer = $this->customerRepository->getCustomer($customerId);
          $customer->update($data);
      }
      else {
          $customerId = $this->customerRepository->addCustomer($data);
          }

    }

    private function delete($form, $values)
    {
      try{
      $this->customerRepository->deleteCustomer($values->customerId);
      }
      catch(Nette\Database\ForeignKeyConstraintViolationException $e){
        $form->addError('Zákazníka není možné odstranit. Pravděpodobně má přiřazené objednávky nebo faktury.');
      }
        //  $customerId = $this->customerRepository->addCustomer($data);
         // $customer = $this->customerRepository->getCustomer($customerId);
          //$this->flashMessage('Bylo zaevidováno nové zařízení: '.$values->name, 'alert-success');

    }

}
