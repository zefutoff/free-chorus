<?php
/* Copyright (C) 2026 Free Chorus contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file       htdocs/custom/choruspro/class/chorusproinvoiceservice.class.php
 * \ingroup    choruspro
 * \brief      Business service for Chorus Pro invoice preparation.
 */

/**
 * Coordinates invoice preparation checks before any Chorus Pro submission.
 *
 * This service intentionally contains no HTML and performs no HTTP call. It
 * only resolves Dolibarr invoice data and returns structured validation data.
 */
class ChorusProInvoiceService
{
    public const DEFAULT_SUPPORTED_CURRENCY = 'EUR';

    /** @var ChorusProClient|null */
    protected $client;

    /** @var ChorusProSubmission|null */
    protected $submission;

    /** @var ChorusProLog|null */
    protected $log;

    /**
     * Constructor.
     *
     * @param ChorusProClient|null     $client     API client
     * @param ChorusProSubmission|null $submission Submission persistence helper
     * @param ChorusProLog|null        $log        Log persistence helper
     */
    public function __construct($client = null, $submission = null, $log = null)
    {
        $this->client = $client;
        $this->submission = $submission;
        $this->log = $log;
    }

    /**
     * Validate whether a Dolibarr invoice can be prepared for Chorus Pro.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array Validation result
     */
    public function validateInvoice($invoice)
    {
        $errors = array();
        $warnings = array();
        $resolvedData = array();

        if (!$this->isInvoiceValidated($invoice)) {
            $errors[] = 'Facture non validée.';
        }

        $thirdparty = $this->getThirdparty($invoice);
        if (!is_object($thirdparty)) {
            $errors[] = 'Tiers absent.';
        }

        $resolvedData['recipient_siret'] = $this->resolveRecipientSiret($invoice);
        if ($resolvedData['recipient_siret'] === '') {
            $errors[] = 'SIRET destinataire absent ou invalide.';
        }

        $resolvedData['structure_id'] = $thirdparty ? $this->getExtraFieldValue($thirdparty, 'choruspro_structure_id') : '';
        if ($resolvedData['structure_id'] === '') {
            $errors[] = 'Structure Chorus Pro inconnue.';
            $warnings[] = 'Identifiant structure non encore mémorisé.';
        }

        $resolvedData['service_code'] = $this->resolveServiceCode($invoice);
        if ($resolvedData['service_code'] === '' && $this->isServiceCodeRequired($invoice)) {
            $errors[] = 'Code service obligatoire mais absent.';
        } elseif ($resolvedData['service_code'] === '') {
            $warnings[] = 'Code service non renseigné mais facultatif.';
        }

        $resolvedData['legal_commitment'] = $this->resolveLegalCommitment($invoice);
        if ($resolvedData['legal_commitment'] === '' && $this->isLegalCommitmentRequired($invoice)) {
            $errors[] = 'Engagement juridique obligatoire mais absent.';
        }

        $resolvedData['pdf'] = $this->resolveInvoicePdf($invoice);
        if (!$resolvedData['pdf']['exists'] && !$resolvedData['pdf']['can_generate']) {
            $errors[] = 'PDF de facture introuvable ou non générable.';
        } elseif (!$resolvedData['pdf']['exists'] && $resolvedData['pdf']['can_generate']) {
            $warnings[] = 'PDF devant être régénéré.';
        }

        if (!$this->hasConsistentAmount($invoice)) {
            $errors[] = 'Montant de facture nul ou incohérent.';
        }

        $resolvedData['currency'] = $this->resolveCurrency($invoice);
        if ($resolvedData['currency'] !== self::DEFAULT_SUPPORTED_CURRENCY) {
            $errors[] = 'Devise non supportée : ' . $resolvedData['currency'] . '.';
        }

        return array(
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'resolvedData' => $resolvedData,
        );
    }

    /**
     * Resolve the recipient SIRET from native Dolibarr thirdparty fields.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return string Normalized 14-digit SIRET or empty string
     */
    public function resolveRecipientSiret($invoice)
    {
        $thirdparty = $this->getThirdparty($invoice);
        if (!is_object($thirdparty)) {
            return '';
        }

        foreach (array('siret', 'idprof2', 'idprof1') as $field) {
            if (!empty($thirdparty->{$field})) {
                $siret = $this->normalizeSiret((string) $thirdparty->{$field});
                if ($siret !== '') {
                    return $siret;
                }
            }
        }

        return '';
    }

    /**
     * Resolve the Chorus Pro service code from invoice then thirdparty extrafields.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return string Service code or empty string
     */
    public function resolveServiceCode($invoice)
    {
        $invoiceCode = $this->getExtraFieldValue($invoice, 'choruspro_code_service');
        if ($invoiceCode !== '') {
            return $invoiceCode;
        }

        $thirdparty = $this->getThirdparty($invoice);
        if (is_object($thirdparty)) {
            return $this->getExtraFieldValue($thirdparty, 'choruspro_code_service_default');
        }

        return '';
    }

    /**
     * Resolve the legal commitment from invoice then thirdparty extrafields.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return string Legal commitment or empty string
     */
    public function resolveLegalCommitment($invoice)
    {
        $invoiceCommitment = $this->getExtraFieldValue($invoice, 'choruspro_engagement_juridique');
        if ($invoiceCommitment !== '') {
            return $invoiceCommitment;
        }

        $thirdparty = $this->getThirdparty($invoice);
        if (is_object($thirdparty)) {
            return $this->getExtraFieldValue($thirdparty, 'choruspro_engagement_default');
        }

        return '';
    }

    /**
     * Resolve the invoice PDF status without generating or uploading anything.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array PDF status with path, exists and can_generate keys
     */
    public function resolveInvoicePdf($invoice)
    {
        $path = $this->resolveInvoicePdfPath($invoice);
        $exists = $path !== '' && is_readable($path);

        return array(
            'path' => $path,
            'exists' => $exists,
            'can_generate' => !$exists && $this->canGenerateInvoicePdf($invoice),
        );
    }

    /**
     * Prepare a local payload preview for future Chorus Pro submission.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array Prepared payload preview
     */
    public function preparePayload($invoice)
    {
        $validation = $this->validateInvoice($invoice);
        if (!$validation['valid']) {
            throw new RuntimeException('Invoice is not ready for Chorus Pro.');
        }

        return $validation['resolvedData'];
    }

    /**
     * Submit an invoice through the future Chorus Pro workflow.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return array Submission result
     */
    public function submitInvoice($invoice)
    {
        throw new BadMethodCallException('Chorus Pro invoice submission workflow is not implemented yet.');
    }

    /**
     * Synchronize the local status of an already submitted invoice.
     *
     * @param ChorusProSubmission|array|object $submission Submission data or object
     * @return array Synchronization result
     */
    public function syncInvoiceStatus($submission)
    {
        throw new BadMethodCallException('Chorus Pro status synchronization is not implemented yet.');
    }

    /**
     * Return whether the invoice status is validated.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return bool True when validated
     */
    protected function isInvoiceValidated($invoice)
    {
        foreach (array('statut', 'status', 'fk_statut') as $field) {
            if (isset($invoice->{$field})) {
                return (int) $invoice->{$field} > 0;
            }
        }

        return false;
    }

    /**
     * Return the invoice thirdparty when loaded.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return object|null Thirdparty object
     */
    protected function getThirdparty($invoice)
    {
        if (isset($invoice->thirdparty) && is_object($invoice->thirdparty)) {
            return $invoice->thirdparty;
        }

        if (isset($invoice->client) && is_object($invoice->client)) {
            return $invoice->client;
        }

        return null;
    }

    /**
     * Normalize and validate a SIRET.
     *
     * @param string $siret Raw SIRET
     * @return string Normalized SIRET or empty string
     */
    protected function normalizeSiret($siret)
    {
        $normalized = preg_replace('/\D/', '', $siret);

        return strlen($normalized) === 14 ? $normalized : '';
    }

    /**
     * Read an extrafield value from a Dolibarr object.
     *
     * @param object $object Dolibarr object
     * @param string $key    Extrafield key without options_ prefix
     * @return string Extrafield value
     */
    protected function getExtraFieldValue($object, $key)
    {
        if (!is_object($object) || empty($object->array_options) || !is_array($object->array_options)) {
            return '';
        }

        foreach (array('options_' . $key, $key) as $candidate) {
            if (isset($object->array_options[$candidate]) && is_scalar($object->array_options[$candidate])) {
                return trim((string) $object->array_options[$candidate]);
            }
        }

        return '';
    }

    /**
     * Return whether service code is mandatory from available extrafields.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return bool True when mandatory
     */
    protected function isServiceCodeRequired($invoice)
    {
        $thirdparty = $this->getThirdparty($invoice);
        if (!is_object($thirdparty)) {
            return false;
        }

        return $this->toBool($this->getExtraFieldValue($thirdparty, 'choruspro_code_service_required'))
            || $this->toBool($this->getExtraFieldValue($thirdparty, 'choruspro_service_required'));
    }

    /**
     * Return whether legal commitment is mandatory from available extrafields.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return bool True when mandatory
     */
    protected function isLegalCommitmentRequired($invoice)
    {
        $thirdparty = $this->getThirdparty($invoice);
        if (!is_object($thirdparty)) {
            return false;
        }

        return $this->toBool($this->getExtraFieldValue($thirdparty, 'choruspro_engagement_required'));
    }

    /**
     * Resolve an invoice PDF path from common Dolibarr fields.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return string PDF path or empty string
     */
    protected function resolveInvoicePdfPath($invoice)
    {
        foreach (array('pdf_path', 'fullpath', 'last_main_doc') as $field) {
            if (!empty($invoice->{$field}) && is_scalar($invoice->{$field})) {
                return (string) $invoice->{$field};
            }
        }

        return '';
    }

    /**
     * Return whether the invoice appears able to generate a PDF later.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return bool True when generation appears possible
     */
    protected function canGenerateInvoicePdf($invoice)
    {
        if (isset($invoice->choruspro_pdf_can_generate)) {
            return $this->toBool($invoice->choruspro_pdf_can_generate);
        }

        return method_exists($invoice, 'generateDocument') && !empty($invoice->modelpdf);
    }

    /**
     * Return whether the invoice amount is consistent.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return bool True when amount is positive
     */
    protected function hasConsistentAmount($invoice)
    {
        foreach (array('total_ttc', 'total_ht') as $field) {
            if (isset($invoice->{$field}) && is_numeric($invoice->{$field})) {
                return (float) $invoice->{$field} > 0;
            }
        }

        return false;
    }

    /**
     * Resolve invoice currency, defaulting to EUR when Dolibarr has no value.
     *
     * @param Facture|object $invoice Dolibarr invoice object
     * @return string Currency code
     */
    protected function resolveCurrency($invoice)
    {
        foreach (array('multicurrency_code', 'currency_code', 'currency') as $field) {
            if (!empty($invoice->{$field}) && is_scalar($invoice->{$field})) {
                return strtoupper((string) $invoice->{$field});
            }
        }

        return self::DEFAULT_SUPPORTED_CURRENCY;
    }

    /**
     * Convert common Dolibarr scalar values to boolean.
     *
     * @param mixed $value Raw value
     * @return bool Boolean value
     */
    protected function toBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
