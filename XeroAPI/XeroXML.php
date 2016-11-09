<?php
namespace App\Controller\Component\XeroAPI;

use Cake\Controller\Component;
/**
 * Created by PhpStorm.
 * User: Vichaya Sunsern
 * Date: 19/08/2016
 * Time: 5:14 AM
 */
class XeroXML
{
    private  $currency = 'AUD';
    public function getTest(){
        return array('time1', 'time2');
    }

    public $type = 'ACCREC';

    public function getTutor()
    {
        return array('tutorq', 'tutor2');
    }

    public function buildTutorInfoXML($tutorInfo,$isSave=false,$contactId='')
    {
        $prefix = 'TUTOR-';
        $xml="";
        $xml = '<Contact>';
        //not needed for new contacts after xero integration update
        if($contactId != ''){
            $xml .= '<ContactID>'.$contactId.'</ContactID>';
        }
        $xml .= '<ContactNumber>'.$prefix.$tutorInfo['id'].'</ContactNumber>';
        if(isset($data['tutor_inactive'])){
            $xml .= '<ContactStatus>'.($data['tutor_inactive'] ? 'ARCHIVED' : 'ACTIVE').'</ContactStatus>';
        } else {
            $xml .= '<ContactStatus>ACTIVE</ContactStatus>';
        }
        $xml .= '<Name>'.$tutorInfo['first_name'].' '.$tutorInfo['last_name'].'</Name>';
        $xml .= '<FirstName>'.$tutorInfo['first_name'].'</FirstName>';
        $xml .= '<LastName>'.$tutorInfo['last_name'].'</LastName>';
        $xml .= '<EmailAddress>'.$tutorInfo['email'].'</EmailAddress>';
        $xml .= '<TaxNumber>'.($tutorInfo['abn'] ? $tutorInfo['abn'] : '').'</TaxNumber>';
        $xml .= '<Phones>';
        $xml .= '<Phone>';
        $xml .= '<PhoneType>DEFAULT</PhoneType>';
        $xml .= '<PhoneNumber>'.$tutorInfo['work_phone'].'</PhoneNumber>';
        $xml .= '<PhoneAreaCode></PhoneAreaCode>';
        $xml .= '<PhoneCountryCode></PhoneCountryCode>';
        $xml .= '</Phone>';
        $xml .= '<Phone>';
        $xml .= '<PhoneType>MOBILE</PhoneType>';
        $xml .= '<PhoneNumber>'.$tutorInfo['mobile'].'</PhoneNumber>';
        $xml .= '<PhoneAreaCode></PhoneAreaCode>';
        $xml .= '<PhoneCountryCode></PhoneCountryCode>';
        $xml .= '</Phone>';
        /*
        $xml .= '<Phone>';
            $xml .= '<PhoneType>MOBILE</PhoneType>';
            $xml .= '<PhoneNumber>'.$data['guardian_mobile'].'</PhoneNumber>';
        $xml .= '</Phone>';
        */
        $xml .= '</Phones>';
        //$xml .= ($isSave)? '<IsSupplier>true</IsSupplier>':'';
        $xml .= '<Addresses>';
        $xml .= '<Address>';
        $xml .= '<AddressType>POBOX</AddressType>';
        $xml .= '<AddressLine1>'.$tutorInfo['school_address_street'].'</AddressLine1>';
        $xml .= '<AddressLine2>'.$tutorInfo['school_address_suburb'].'</AddressLine2>';
        $xml .= '<Region>'.$tutorInfo['school_address_state'].'</Region>';
        $xml .= '<PostalCode>'.$tutorInfo['school_address_postcode'].'</PostalCode>';
        $xml .= '<Country>Australia</Country>';
        $xml .= '</Address>';
        $xml .= '<Address>';
        $xml .= '<AddressType>STREET</AddressType>';
        $xml .= '<AddressLine1>'.$tutorInfo['address_street'].'</AddressLine1>';
        $xml .= '<AddressLine2>'.$tutorInfo['address_suburb'].'</AddressLine2>';
        $xml .= '<Region>'.$tutorInfo['address_state'].'</Region>';
        $xml .= '<PostalCode>'.$tutorInfo['address_postcode'].'</PostalCode>';
        $xml .= '<Country>Australia</Country>';
        $xml .= '</Address>';
        $xml .= '</Addresses>';
        $xml .= '</Contact>';
        return $xml;
    }

    public function buildStudentInfoXML($studentInfo,$contactId='')
    {
        $prefix = 'STUDENT-';
        debug($studentInfo['id']);
        $xml = '<Contact>';
        if($contactId != ''){
            $xml .= '<ContactID>'.$contactId.'</ContactID>';
        }
        $xml .= '<ContactNumber>'.$prefix.$studentInfo['id'].'</ContactNumber>';
        if(isset($data['student_inactive'])){
            $xml .= '<ContactStatus>'.($studentInfo['student_inactive'] ? 'ARCHIVED' : 'ACTIVE').'</ContactStatus>';
        } else {
            $xml .= '<ContactStatus>ACTIVE</ContactStatus>';
        }
        $xml .= '<Name>'.$studentInfo['first_name'].' '.$studentInfo['last_name'].'</Name>';
        $xml .= '<FirstName>'.$studentInfo['first_name'].'</FirstName>';
        $xml .= '<LastName>'.$studentInfo['last_name'].'</LastName>';
        //$xml .= '<Name>'.$studentInfo['guardian_first_name'].' '.$studentInfo['guardian_last_name'].'</Name>';
        $xml .= '<EmailAddress>'.$studentInfo['guardian_email'].'</EmailAddress>';
        $xml .= '<ContactPersons>';
        $xml .= '<ContactPerson>';
        $xml .= '<FirstName>'.$studentInfo['guardian_first_name'].'</FirstName>';
        $xml .= '<LastName>'.$studentInfo['guardian_last_name'].'</LastName>';
        $xml .= (!empty($studentInfo['student_email']))? '<EmailAddress>'.$studentInfo['student_email'].'</EmailAddress>' : '';
        $xml .= '<IncludeInEmails>false</IncludeInEmails>';
        $xml .= '</ContactPerson>';
        $xml .= '</ContactPersons>';
        $xml .= '<Phones>';
        $xml .= '<Phone>';
        $xml .= '<PhoneType>DEFAULT</PhoneType>';
        $xml .= '<PhoneNumber>'.$studentInfo['guardian_phone'].'</PhoneNumber>';
        $xml .= '</Phone>';
        $xml .= '<Phone>';
        $xml .= '<PhoneType>MOBILE</PhoneType>';
        $xml .= '<PhoneNumber>'.$studentInfo['guardian_mobile'].'</PhoneNumber>';
        $xml .= '</Phone>';
        $xml .= '</Phones>';
        $xml .= '<Addresses>';
        $xml .= '<Address>';
        $xml .= '<AddressType>POBOX</AddressType>';
        $xml .= '<AddressLine1>'.$studentInfo['guardian_address_street'].'</AddressLine1>';
        $xml .= '<AddressLine2>'.$studentInfo['guardian_address_suburb'].'</AddressLine2>';
        $xml .= '<Region>'.$studentInfo['guardian_address_state'].'</Region>';
        $xml .= '<PostalCode>'.$studentInfo['guardian_address_postcode'].'</PostalCode>';
        $xml .= '<Country>Australia</Country>';
        $xml .= '</Address>';
        $xml .= '<Address>';
        $xml .= '<AddressType>STREET</AddressType>';
        $xml .= '<AddressLine1>'.$studentInfo['address_street'].'</AddressLine1>';
        $xml .= '<AddressLine2>'.$studentInfo['address_suburb'].'</AddressLine2>';
        $xml .= '<Region>'.$studentInfo['address_state'].'</Region>';
        $xml .= '<PostalCode>'.$studentInfo['address_postcode'].'</PostalCode>';
        $xml .= '<Country>Australia</Country>';
        $xml .= '</Address>';
        $xml .= '</Addresses>';
        $xml .= '</Contact>';

        return $xml;
    }

    public function buildGuardianInfoXML($guardianInfo)
    {
        $prefix = 'GUARD-';
        $xml = '<Contact>';
        $xml .= '<ContactNumber>'.$prefix.$guardianInfo['id'].'</ContactNumber>';
        if(isset($data['student_inactive'])){
            $xml .= '<ContactStatus>'.($guardianInfo['student_inactive'] ? 'ARCHIVED' : 'ACTIVE').'</ContactStatus>';
        } else {
            $xml .= '<ContactStatus>ACTIVE</ContactStatus>';
        }
        if (empty($guardianInfo['guardian_first_name']) || empty($guardianInfo['guardian_last_name'])) return false;
        $xml .= '<Name>'.$guardianInfo['guardian_first_name'].' '.$guardianInfo['guardian_last_name'].'</Name>';
        $xml .= '<FirstName>'.$guardianInfo['guardian_first_name'].'</FirstName>';
        $xml .= '<LastName>'.$guardianInfo['guardian_last_name'].'</LastName>';
        if (!empty($guardianInfo['guardian_email']))
            $xml .= '<EmailAddress>'.$guardianInfo['guardian_email'].'</EmailAddress>';
        if (isset($guardianInfo['contact_persons'])&&!empty($guardianInfo['contact_persons'])){
            $xml .= '<ContactPersons>';
            foreach($guardianInfo['contact_persons'] as $contact_person){
                $xml .= '<ContactPerson>';
                $xml .= '<FirstName>'.$contact_person['first_name'].'</FirstName>';
                $xml .= '<LastName>'.$contact_person['last_name'].'</LastName>';
                $xml .= '<EmailAddress>'.$contact_person['student_email'].'</EmailAddress>';
                $xml .= '<IncludeInEmails>false</IncludeInEmails>';
                $xml .= '</ContactPerson>';
            }
            $xml .= '</ContactPersons>';
        }
        if (!empty($guardianInfo['guardian_phone']) || !empty($guardianInfo['guardian_mobile'])) {
            $xml .= '<Phones>';
            if (!empty($guardianInfo['guardian_phone'])) {
                $xml .= '<Phone>';
                $xml .= '<PhoneType>DEFAULT</PhoneType>';
                $xml .= '<PhoneNumber>'.$guardianInfo['guardian_phone'].'</PhoneNumber>';
                $xml .= '</Phone>';
            }
            if (!empty($guardianInfo['guardian_mobile'])) {
                $xml .= '<Phone>';
                $xml .= '<PhoneType>MOBILE</PhoneType>';
                $xml .= '<PhoneNumber>'.$guardianInfo['guardian_mobile'].'</PhoneNumber>';
                $xml .= '</Phone>';
            }
            $xml .= '</Phones>';
        }
        if (!empty($guardianInfo['guardian_address_street']) || !empty($guardianInfo['guardian_address_suburb']) || !empty($guardianInfo['guardian_address_state'])|| !empty($guardianInfo['guardian_address_postcode'])) {
            $xml .= '<Addresses>';
            $xml .= '<Address>';
            $xml .= '<AddressType>POBOX</AddressType>';
            if (!empty($guardianInfo['guardian_address_street']))
                $xml .= '<AddressLine1>'.$guardianInfo['guardian_address_street'].'</AddressLine1>';
            if (!empty($data['guardian_address_suburb']))
                $xml .= '<AddressLine2>'.$guardianInfo['guardian_address_suburb'].'</AddressLine2>';
            if (!empty($data['guardian_address_state']))
                $xml .= '<Region>'.$guardianInfo['guardian_address_state'].'</Region>';
            if (!empty($data['guardian_address_postcode']))
                $xml .= '<PostalCode>'.$guardianInfo['guardian_address_postcode'].'</PostalCode>';
            $xml .= '<Country>Australia</Country>';
            $xml .= '</Address>';
            $xml .= '</Addresses>';
        }

        $xml .= '</Contact>';
        return $xml;
    }

    public function buildInvoiceXML($invoiceDetail)
    {
        $totalXml = '';
        if($this->type != 'ACCREC')
        {
            $invoiceDetail['invoiceDate'] = date('Y-m-d', strtotime($invoiceDetail['TimeSheet']['date_created']));
            //$startDate = date('Y-m-d', strtotime($invoiceDetail['TimeSheet']['period_start_date']));
            $invoiceDetail['dueDate'] = $invoiceDetail['TimeSheet']['period_end_date'];
            if (isset($invoiceDetail['TimesheetDate'])&&!empty($invoiceDetail['TimesheetDate'])) {
                $invoiceDetail['dueDate'] = $invoiceDetail['TimesheetDate']['period_end_date'];
            }
            //due date is the next pay cycle if late
            //submitt date past due date
            $invoiceDetail['dueDate']=date('Y-m-d', strtotime('+1 day', strtotime( $invoiceDetail['dueDate']) ));
            if ($invoiceDetail['TimeSheet']['late']){
                $invoiceDetail['dueDate'] = date('Y-m-d', strtotime('+14 day', strtotime( $invoiceDetail['dueDate'])));
            }
            $subtotal=0;
            //debug($invoiceDetail['TimesheetLesson']);
            $lineItem='';
            foreach($invoiceDetail['TimesheetLesson'] as $timesheetLesson){
                $c = strtolower($timesheetLesson['subject_name']);
                if (strpos($c,'assessment') !== false) {
                    $accountCode=51010.1;
                } elseif ($timesheetLesson['class_year'] <= 10){
                    $accountCode=51010.2;
                } elseif ($timesheetLesson['class_year'] > 10){
                    $accountCode=51010.3;
                }
                $itemTotal = $timesheetLesson['lesson_duration'] * $timesheetLesson['payrate'];
                $subtotal += $itemTotal;

                $lineItem .= '<LineItem>';
                $lineItem .= '<Description>'.$timesheetLesson['student_name'].'-'.$timesheetLesson['subject_name'].'----- Yr'.$timesheetLesson['class_year'].'</Description>';
                $lineItem .= '<Quantity>'.$timesheetLesson['lesson_duration'].'</Quantity>';
                $lineItem .= '<UnitAmount>'.$timesheetLesson['payrate'].'</UnitAmount>';
                if($timesheetLesson['amount'] != $itemTotal){
                    $lineItem .= '<LineAmount>'.$itemTotal.'</LineAmount>';
                } else {
                    $lineItem .= '<LineAmount>'.$timesheetLesson['amount'].'</LineAmount>';
                }
                $lineItem .= '<TaxType>BASEXCLUDED</TaxType>';
                $lineItem .= '<AccountCode>'.$accountCode.'</AccountCode>';
                $lineItem .= '</LineItem>';
            }

            $totalXml .= '<SubTotal>'.$subtotal.'</SubTotal>';
            $totalXml .= '<TotalTax>0</TotalTax>';
            $totalXml .= '<Total>'.$subtotal.'</Total>';
        }else{
            $lessons = $invoiceDetail['Lessons'];

            $lineItem='';
            //debug($lessons);
            if(!isset($lessons[0]))
            {
                $lineItem .= $this->createInvoiceLineItem($lessons);
            }else{
                foreach ($lessons as $key=>$lesson):
                    //debug($lesson);
                    $lineItem .= $this->createInvoiceLineItem($lesson);
                endforeach;

            }

            $invoiceDetail['invoiceDate'] = date('Y-m-d', strtotime($invoiceDetail['TimeSheets']['date_created']));
            //ajt
            //  $dueDate = $data['TimeSheet']['period_start_date'];
            //  $dueDate=date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d')) ));


            if (empty($invoiceDate)) {
                $invoiceDetail['invoiceDate'] = date('Y-m-d');
            }
            if(empty($dueDate)) {
                $invoiceDetail['dueDate']=date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d')) ));
            }
            else{
                $invoiceDetail['dueDate'] = $invoiceDetail['Lesson']['duedate'];
            }
        }

        $InvoiceNumber = (($this->type == 'ACCREC')? $this->genStudentXeroInvoiceNumber($invoiceDetail['id']) : $this->genTimeSheetXeroInvoiceNumber($invoiceDetail['TimeSheet']));
        $xml="";
        $xml = '<Invoice>';
        $xml .= '<Type>'.$this->type.'</Type>';
        $xml .= '<InvoiceNumber>'.$InvoiceNumber.'</InvoiceNumber>';
        $xml .=  ($this->type == 'ACCREC')? $this->buildGuardianInfoXML($invoiceDetail['Guardians']->toArray()) : $this->buildTutorInfoXML($invoiceDetail['Tutor'] );
        $xml .= '<Date>'.$invoiceDetail['invoiceDate'].'</Date>';
        $xml .= '<DueDate>'.$invoiceDetail['dueDate'].'</DueDate>';
        $xml .= '<CurrencyCode>'.$this->currency.'</CurrencyCode>';

        $xml .= '<Reference>'.(($this->type == 'ACCREC')? $InvoiceNumber : '').'</Reference>';
        $xml .= '<LineAmountTypes>'.(($this->type == 'ACCREC')? 'Inclusive' : 'Exclusive' ).'</LineAmountTypes>';
        $xml .= '<LineItems>';

        $xml .= $lineItem;
        $xml .= '</LineItems>';
        $xml .=$totalXml;
        $xml .= '</Invoice>';
        //debug($xml);
        return $xml;
    }

    private function createInvoiceLineItem($lesson){
        $lineItem ='';
        if (empty($lesson['student_name']) || empty($lesson['subject'])  || empty($lesson['payrate'])) {
            return false;
        } else {
            $lineItem .= '<LineItem>';
            //     $lineItem .= '<ItemCode>GB1</ItemCode>'; //ajt
            //	$lineItem .= '<Description>'.$lesson['student_name'].'-'.$lesson['subject'].'----- Yr'.$lesson['class_year'].'</Description>';
            $lineItem .= '<Description>' . $lesson['description'] . '</Description>';

            $lineItem .= '<Quantity>1</Quantity>';
            //$lineItem .= '<ItemCode>' . $key. '</ItemCode>';
            $lineItem .= '<UnitAmount>' . $lesson['payrate'] . '</UnitAmount>';
            //$lineItem .= '<TaxType>BASEXCLUDED</TaxType>';
            $lineItem .= '<TaxType>OUTPUT</TaxType>';

            //using 200 account code it add 10% GST
            $lineItem .= '<AccountCode>' . $lesson['account_code'] . '</AccountCode>';
            //  $lineItem .= '<AccountCode>200</AccountCode>';


            $lineItem .= '</LineItem>';
        }

        return $lineItem;
    }

    public function genInvoiceID($id)
    {
        return ($id*10);
    }
    public function genTimeSheetXeroInvoiceNumber($timeSheetData)
    {
        return $timeSheetData['invoice'].'-'.$timeSheetData['id'];
    }

    public function genPrepaidInvoiceNumber($lesson)
    {
        return $this->genStudentXeroInvoiceNumber($lesson['term_id'].$lesson['student_id']);
    }

    public function genStudentXeroInvoiceNumber($invoiceId)
    {
        return 'INV-'.$this->genInvoiceID($invoiceId);
    }

    public function setAsSupplierInvoice(){
        $this->type = 'ACCPAY';
    }

    public function setAsCustomerInvoice(){
        $this->type = 'ACCREC';
    }

    public function buildCreditNoteXML($contactID,$invoiceNumber,$invoice=array())
    {
        $xml = '<CreditNote>';
        $xml .= '<Type>ACCRECCREDIT</Type>';
        $xml .= '<CreditNoteNumber>'.$invoiceNumber.'</CreditNoteNumber>';
        $xml .= '<Contact>';
        $xml .= '<ContactID>'.$contactID.'</ContactID>';
        $xml .= '</Contact>';

        $lineItemsData = $invoice['LineItems'];
        //credit note with line item
        if(!empty(array_filter($lineItemsData)))
        {
            $lineItem = '';
            foreach ($lineItemsData as $item):
                $lineItem .= '<LineItem>';
                $lineItem .= '<Description>'.$item['Description'].'</Description>';
                $lineItem .= '<Quantity>'.$item['Quantity'].'</Quantity>';
                $lineItem .= '<UnitAmount>'.$item['UnitAmount'].'</UnitAmount>';
                $lineItem .= '<TaxType>'.$item['TaxType'].'</TaxType>';
                $lineItem .= '<TaxAmount>'.$item['TaxAmount'].'</TaxAmount>';
                $lineItem .= '<LineAmount>'.$item['LineAmount'].'</LineAmount>';
                $lineItem .= (isset($item['AccountCode']))? '<AccountCode>'.$item['AccountCode'].'</AccountCode>' : '<AccountCode/>';
                $lineItem .= '<Tracking/>';
                $lineItem .= '</LineItem>';

            endforeach;

            if(!empty($lineItem))
            {
                $xml .= '<Date>'.date('Y-m-d').'</Date>';
                $xml .= '<LineAmountTypes>Exclusive</LineAmountTypes>';
                $xml .= '<LineItems>';
                $xml .= $lineItem;
                $xml .= '</LineItems>';
            }
        }

        //$xml .= '<SubTotal>'.$invoice['SubTotal'].'</SubTotal>';
        //$xml .= '<TotalTax>'.$invoice['TotalTax'].'</TotalTax>';
        //$xml .= '<Total>'.$invoice['Total'].'</Total>';
        $xml .= '</CreditNote>';
        //debug($xml);
        return $xml;
    }

    public function buildDeleteInvoiceXML($invoiceID)
    {
        $xml = '<Invoice>';
        $xml .= ' <InvoiceNumber>'.$invoiceID.'</InvoiceNumber>';
        $xml .= ' <Status>DELETED</Status>';
        $xml .= '</Invoice>';
        return $xml;
    }



    public function buildDeleteContact($contactID)
    {
        $xml = '<Contact>';
        $xml .= ' <ContactID>'.$contactID.'</ContactID>';
        $xml .= ' <ContactStatus>ARCHIVED</ContactStatus>';
        $xml .= '</Contact>';
        return $xml;
    }
}