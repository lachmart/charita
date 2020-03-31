<?php

/**
 *
 *
 */

namespace FrontModule;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Repository\CustomerRepository;
use App\Model\Repository\OrderRepository;
use App\Forms\ICustomerFormFactory;
use App\Forms\CustomerForm;
use App\Components\ICustomerControlFactory;
use App\Components\IOrderControlFactory;
use App\Components\IInvoiceControlFactory;

//define("ALL", -1);

class CustomerPresenter extends BasePresenter
{
    /** @var CustomerRepository */
    private $customerRepository;

    /** @var OrderRepository */
    private $orderRepository;

    /**
     * @var ICustomerControlFactory
     * @inject
     */
    public $customerControlFactory;
    /**
     * @var IInvoiceControlFactory
     * @inject
     */
    public $invoiceControlFactory;
    /**
     * @var IOrderControlFactory
     * @inject
     */
    public $orderControlFactory;

    /** @var \ICustomerFormFactory */
    private $customerFormFactory;

    /** @persistent */
    public $customerId;

    public function __construct(CustomerRepository $customerRepository,OrderRepository $orderRepository,
                                CustomerForm $customerFormFactory)
    {
        parent::__construct();
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->customerFormFactory =  $customerFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->customerTypeFilterId = ALL;

    }


    function renderDefault()
    {
        //$this->template->customerTypes = $this->customerRepository->getAllCustomerTypes();
        //\var_dump($this->user->getIdentity());
    }


    function renderCreate($customerTypeId)
    {
      $this->template->showInvoiceCreate = 0;
      $this->customerId = 0;
       // $this->template->customerAttributes = $this->customerRepository->getAllCustomerAttributesForCustomerType($customerTypeId);
        //$this->customerTypeId = $customerTypeId;
    }


    function renderDetail($customerId)
    {
        $this->template->showInvoiceCreate = 0;
        $customer = $this->customerRepository->getCustomer($customerId);
        if($customer){
          $this->customerId = $customerId;
          $this->template->customer = $customer;
          $this->template->showInvoiceCreate = count($this->orderRepository->getOrdersWithoutInvoice($customerId));
          $customerBasicAttributes = $customer->toArray();
          $this['customerForm']->setDefaults($customerBasicAttributes);

        }else {
          $this->redirect("Customer:");
        }
    }


    public function handleCreateCustomer()
    {

        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }

    public function handleCreateLogEntry()
    {
      //  if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
      //  }
    }

    protected function createComponentCustomerForm()
    {

        //TODO customer
        //$customerTypeId = $this->customerTypeId;


       // if(!$customerTypeId) $customerTypeId = 0;

        $form = $this->customerFormFactory->create($this->customerId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            if($this->customerId){
              $this->flashMessage('Zákazník byl aktualizován a změny uloženy.', 'alert-success');
              $this->redirect('Customer:detail');
            }
            else{
              $this->flashMessage('Zákazník byl vytvořen.', 'alert-success');
              $this->redirect('Customer:');
            }
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Zákazník byl odstraněn.', 'alert-success');
            $this->redirect('Customer:');
          }

        };



        return $form;

    }
    protected function createComponentCustomerLogEntryForm()
    {
        $form = $this->customerLogEntryFormFactory->create($this->customerId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('Customer:');
          }
        };
        return $form;

    }




    public function createComponentCustomerControl()
    {
        return $this->customerControlFactory->create();
    }


    public function createComponentOrderControl()
    {
        return $this->orderControlFactory->create();
    }
    public function createComponentInvoiceControl()
    {
        return $this->invoiceControlFactory->create();
    }



}
