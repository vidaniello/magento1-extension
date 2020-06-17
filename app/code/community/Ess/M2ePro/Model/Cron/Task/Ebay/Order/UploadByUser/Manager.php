<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser_Manager
{
    /** @var string */
    protected $_identifier;

    //########################################

    /**
     * @return bool
     * @throws Exception
     */
    public function isEnabled()
    {
        return $this->getFromDate() !== null && $this->getToDate() !== null;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isInProgress()
    {
        return $this->isEnabled() && $this->getCurrentFromDate() !== null;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isCompleted()
    {
        return $this->isInProgress() &&
               $this->getCurrentFromDate()->getTimestamp() == $this->getToDate()->getTimestamp();
    }

    //----------------------------------------

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getFromDate()
    {
        $date = $this->getSettings('from_date');
        if ($date === null) {
            return $date;
        }

        return new DateTime($date, new \DateTimeZone('UTC'));
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getToDate()
    {
        $date = $this->getSettings('to_date');
        if ($date === null) {
            return $date;
        }

        return new DateTime($date, new \DateTimeZone('UTC'));
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getCurrentFromDate()
    {
        $date = $this->getSettings('current_from_date');
        if ($date === null) {
            return $date;
        }

        return new DateTime($date, new \DateTimeZone('UTC'));
    }

    /**
     * @return string|null
     */
    public function getJobToken()
    {
        return $this->getSettings('job_token');
    }

    //----------------------------------------

    /**
     * @param string|false $fromDate
     * @param string|false $toDate
     */
    public function setFromToDates($fromDate = false, $toDate = false)
    {
        $this->validate($fromDate, $toDate);

        $this->setSettings('from_date', $fromDate);
        $this->setSettings('to_date', $toDate);
    }

    /**
     * @param string $fromDate
     * @param string $toDate
     * @return bool
     */
    public function validate($fromDate, $toDate)
    {
        $from = new DateTime($fromDate, new \DateTimeZone('UTC'));
        $to   = new DateTime($toDate, new \DateTimeZone('UTC'));

        if ($from->getTimestamp() > $to->getTimestamp()) {
            throw new Ess_M2ePro_Model_Exception_Logic('From date is bigger than To date.');
        }

        $now = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        if ($from->getTimestamp() > $now || $to->getTimestamp() > $now) {
            throw new Ess_M2ePro_Model_Exception_Logic('Dates you provided are bigger than current.');
        }

        if ($from->diff($to)->days > 30) {
            throw new Ess_M2ePro_Model_Exception_Logic('From - to interval provided is too big. (Max: 30 days)');
        }

        $minDate = new DateTime('now', new DateTimeZone('UTC'));
        $minDate->modify('-90 days');

        if ($from->getTimestamp() < $minDate->getTimestamp()) {
            throw new Ess_M2ePro_Model_Exception_Logic('From date provided is too old. (Max: 90 days)');
        }

        return true;
    }

    /**
     * @param string $currentFromDate
     */
    public function setCurrentFromDate($currentFromDate)
    {
        $this->setSettings('current_from_date', $currentFromDate);
    }

    /**
     * @param string|null $jobToken
     */
    public function setJobToken($jobToken)
    {
        $this->setSettings('job_token', $jobToken);
    }

    //----------------------------------------

    public function clear()
    {
        $this->removeSettings();
    }

    //########################################

    public function setIdentifier($id)
    {
        $this->_identifier = $id;
        return $this;
    }

    public function getIdentifier()
    {
        return $this->_identifier;
    }

    //----------------------------------------

    public function setIdentifierByAccount(Ess_M2ePro_Model_Account $account)
    {
        return $this->setIdentifier($account->getChildObject()->getUserId());
    }

    //########################################

    protected function getSettings($key = null)
    {
        $registry = Mage::getModel('M2ePro/Registry')
            ->loadByKey("/ebay/orders/upload_by_user/{$this->_identifier}/");

        $value = $registry->getValueFromJson();
        if ($key === null) {
            return $value;
        }

        return isset($value[$key]) ? $value[$key] : null;
    }

    protected function setSettings($key, $value)
    {
        $registry = Mage::getModel('M2ePro/Registry')
            ->loadByKey("/ebay/orders/upload_by_user/{$this->_identifier}/");

        $settings = $registry->getValueFromJson();
        $settings[$key] = $value;

        $registry->setValue($settings);
        $registry->save();
    }

    protected function removeSettings()
    {
        $registry = Mage::getModel('M2ePro/Registry')
            ->loadByKey("/ebay/orders/upload_by_user/{$this->_identifier}/");
        $registry->delete();
    }

    //########################################
}
