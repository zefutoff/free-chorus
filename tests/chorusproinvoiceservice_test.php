<?php

require __DIR__ . '/../htdocs/custom/choruspro/class/chorusproinvoiceservice.class.php';

function assertTrue($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

function makeInvoice(array $overrides = array())
{
    $thirdparty = (object) array(
        'idprof2' => '11000201100044',
        'array_options' => array(
            'options_choruspro_structure_id' => '123456',
        ),
    );

    $invoice = (object) array(
        'element' => 'facture',
        'statut' => 1,
        'thirdparty' => $thirdparty,
        'array_options' => array(),
        'pdf_path' => __DIR__ . '/fixtures/invoice.pdf',
        'total_ttc' => 120.0,
        'multicurrency_code' => 'EUR',
    );

    foreach ($overrides as $key => $value) {
        if ($key === 'thirdparty_options') {
            $invoice->thirdparty->array_options = array_merge($invoice->thirdparty->array_options, $value);
            continue;
        }

        if ($key === 'invoice_options') {
            $invoice->array_options = array_merge($invoice->array_options, $value);
            continue;
        }

        $invoice->{$key} = $value;
    }

    return $invoice;
}

@mkdir(__DIR__ . '/fixtures', 0777, true);
file_put_contents(__DIR__ . '/fixtures/invoice.pdf', 'PDF');
$service = new ChorusProInvoiceService();

$result = $service->validateInvoice(makeInvoice(array('statut' => 0)));
assertTrue(!$result['valid'], 'Draft invoice must be invalid.');
assertTrue(in_array('Facture non validée.', $result['errors'], true), 'Draft invoice error missing.');

$invoice = makeInvoice();
$invoice->thirdparty->idprof2 = '';
$result = $service->validateInvoice($invoice);
assertTrue(!$result['valid'], 'Invoice without SIRET must be invalid.');
assertTrue(in_array('SIRET destinataire absent ou invalide.', $result['errors'], true), 'Missing SIRET error missing.');

$result = $service->validateInvoice(makeInvoice());
assertTrue($result['resolvedData']['recipient_siret'] === '11000201100044', 'Valid SIRET must be resolved.');

$result = $service->validateInvoice(makeInvoice(array(
    'thirdparty_options' => array('options_choruspro_code_service_required' => '1'),
)));
assertTrue(!$result['valid'], 'Missing mandatory service code must be invalid.');
assertTrue(in_array('Code service obligatoire mais absent.', $result['errors'], true), 'Mandatory service code error missing.');

$result = $service->validateInvoice(makeInvoice(array(
    'thirdparty_options' => array('options_choruspro_engagement_required' => '1'),
)));
assertTrue(!$result['valid'], 'Missing mandatory legal commitment must be invalid.');
assertTrue(in_array('Engagement juridique obligatoire mais absent.', $result['errors'], true), 'Mandatory legal commitment error missing.');

$result = $service->validateInvoice(makeInvoice(array('pdf_path' => __DIR__ . '/fixtures/missing.pdf')));
assertTrue(!$result['valid'], 'Missing PDF must be invalid when not generable.');
assertTrue(in_array('PDF de facture introuvable ou non générable.', $result['errors'], true), 'Missing PDF error missing.');

$result = $service->validateInvoice(makeInvoice(array(
    'invoice_options' => array(
        'options_choruspro_code_service' => 'FIN',
        'options_choruspro_engagement_juridique' => 'EJ-2026',
    ),
)));
assertTrue($result['valid'], 'Complete invoice must be ready.');
assertTrue($result['resolvedData']['service_code'] === 'FIN', 'Service code must be resolved from invoice.');
assertTrue($result['resolvedData']['legal_commitment'] === 'EJ-2026', 'Legal commitment must be resolved from invoice.');

@unlink(__DIR__ . '/fixtures/invoice.pdf');
@rmdir(__DIR__ . '/fixtures');

echo "ChorusProInvoiceService tests OK\n";
