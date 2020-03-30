<?php

/**
 *
 *
 */

namespace App\Forms;

use Nette;
use Nette\Application\UI;
use Nette\Forms\Controls;
use App\Model\Repository\InvoiceRepository;
use App\Model\Repository\OrderRepository;


class InvoiceForm
{

    /** @var InvoiceRepository */
    private $invoiceRepository;
    /** @var OrderRepository */
    private $orderRepository;




    public function __construct(InvoiceRepository $invoiceRepository,OrderRepository $orderRepository)
    {
        //parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
    }




    public function create($invoiceId)
    {
        $form = new Nette\Application\UI\Form;


        $form->getElementPrototype()->class('ajax');

        $form->addGroup('');
        $form->addText('invoiceId', 'Id')->setAttribute('readonly','readonly');
        $form->addText('created', 'Vytvořeno')->setAttribute('readonly','readonly');
        $form->addText('total', 'cena')->setAttribute('readonly','readonly');
        $form->addText('fk_customer', 'Zákazník')->setDefaultValue($invoiceId)->setAttribute('readonly','readonly');
        $form->addText('customerName', 'Zákazník')->setDisabled();


        $form->addSubmit('submit', 'Přepočítat fakturu');
        $form->addSubmit('delete', 'Odstranit fakturu');
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
      $invoiceId = $values->invoiceId;


      $data = $values;
    //  $data['id'] = 1;
      $data['total']=$this->orderRepository->getTotalInvoice($invoiceId);



      if ($invoiceId) {
          $invoice = $this->invoiceRepository->getInvoice($invoiceId);
          $invoice->update($data);
      }
      else {
          $data['created']=date('Y-m-d H:i:s',time());
          $invoiceId = $this->invoiceRepository->addInvoice($data);
          }

    }

    private function delete($form, $values)
    {
      try{
      $this->invoiceRepository->deleteInvoice($values->invoiceId);
      }
      catch(Nette\Database\ForeignKeyConstraintViolationException $e){
        $form->addError('Fakturu není možné odstranit. Pravděpodobně má přiřazené objednávky.');
      }
        //  $invoiceId = $this->invoiceRepository->addInvoice($data);
         // $invoice = $this->invoiceRepository->getInvoice($invoiceId);
          //$this->flashMessage('Bylo zaevidováno nové zařízení: '.$values->name, 'alert-success');

    }

}
