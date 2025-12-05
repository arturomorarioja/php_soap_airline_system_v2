<?php
// client.php

declare(strict_types=1);

/**
 * Pretty print an XML string.
 */
function pretty_print_xml(string $xml): string
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (@$dom->loadXML($xml) === false) {
        return $xml;
    }

    return $dom->saveXML();
}

$host = getenv('SOAP_SERVER_HOST') ?: '127.0.0.1';
$port = getenv('SOAP_SERVER_PORT') ?: '8080';

$baseUrl = 'http://' . $host . ':' . $port;

$wsdlUrl     = $baseUrl . '/AirlineService.wsdl';
$endpointUrl = $baseUrl . '/server.php';

$options = [
    'trace'      => true,
    'exceptions' => true,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'location'   => $endpointUrl,
];

try {
    $client = new SoapClient($wsdlUrl, $options);

    /**
     * SearchFlights call
     */
    $requestPayload = [
        'origin'        => 'CPH',
        'destination'   => 'MAD',
        'departureDate' => '2025-03-15',
        'returnDate'    => '2025-03-22',
        'passengers'    => [
            'adultCount'  => 1,
            'childCount'  => 0,
            'infantCount' => 0,
        ],
        'cabin'         => 'ECONOMY',
    ];

    $result = $client->__soapCall('SearchFlights', [$requestPayload]);

    echo "SearchFlights result\n";
    echo "====================\n\n";

    if (isset($result->flights)) {
        $flights = is_array($result->flights) ? $result->flights : [$result->flights];

        if (count($flights) === 0) {
            echo "No flights returned.\n\n";
        } else {
            foreach ($flights as $index => $flight) {
                echo 'Flight ' . ($index + 1) . ":\n";
                echo '  Carrier       : ' . ($flight->carrier ?? '') . "\n";
                echo '  Flight number : ' . ($flight->flightNumber ?? '') . "\n";
                echo '  Departure     : ' . ($flight->departDateTime ?? '') .
                    ' from ' . ($flight->origin ?? '') . "\n";
                echo '  Arrival       : ' . ($flight->arriveDateTime ?? '') .
                    ' at ' . ($flight->destination ?? '') . "\n";

                if (isset($flight->price)) {
                    echo '  Price         : ' .
                        ($flight->price->amount ?? '') . ' ' .
                        ($flight->price->currency ?? '') . "\n";
                }

                echo "\n";
            }
        }
    } else {
        echo "No flights element found in response.\n\n";
    }

    $lastRequest  = $client->__getLastRequest();
    $lastResponse = $client->__getLastResponse();

    echo "=== LAST REQUEST (SearchFlights) ===\n";
    echo pretty_print_xml($lastRequest) . "\n";

    echo "=== LAST RESPONSE (SearchFlights) ===\n";
    echo pretty_print_xml($lastResponse) . "\n\n";

    /**
     * PurchaseTicket call
     */
    $purchasePayload = [
        'pnr' => 'AB12CD',
        'payment' => [
            'method' => 'CARD',
            'card' => [
                'holderName'  => 'John Doe',
                'number'      => '4111111111111111',
                'expiryMonth' => 12,
                'expiryYear'  => 2030,
                'cvv'         => '123',
            ],
            'amount'   => 350.00,
            'currency' => 'EUR',
        ],
        'billingEmail' => 'john.doe@example.com',
    ];

    $purchaseResult = $client->__soapCall('PurchaseTicket', [$purchasePayload]);

    echo "PurchaseTicket result\n";
    echo "======================\n\n";

    if (isset($purchaseResult->tickets)) {
        $tickets = is_array($purchaseResult->tickets)
            ? $purchaseResult->tickets
            : [$purchaseResult->tickets];

        if (count($tickets) === 0) {
            echo "No tickets returned.\n\n";
        } else {
            echo "Tickets:\n";
            foreach ($tickets as $index => $ticket) {
                echo '  Ticket ' . ($index + 1) . ":\n";
                echo '    Number : ' . ($ticket->ticketNumber ?? '') . "\n";
                echo '    Status : ' . ($ticket->status ?? '') . "\n";
                echo '    Issued : ' . ($ticket->issuedAt ?? '') . "\n";
            }
            echo "\n";
        }
    } else {
        echo "No tickets element found in response.\n\n";
    }

    if (isset($purchaseResult->booking)) {
        $b = $purchaseResult->booking;
        echo "Booking:\n";
        echo '  PNR    : ' . ($b->pnr ?? '') . "\n";
        echo '  Status : ' . ($b->status ?? '') . "\n";

        if (isset($b->passengers)) {
            $passengers = is_array($b->passengers)
                ? $b->passengers
                : [$b->passengers];

            if (count($passengers) === 0) {
                echo "  No passengers.\n";
            } else {
                echo "  Passengers:\n";
                foreach ($passengers as $index => $p) {
                    $name    = $p->name ?? null;
                    $first   = $name->first ?? '';
                    $last    = $name->last ?? '';
                    $dob     = isset($p->dob) ? $p->dob : '(not provided)';
                    $loyalty = isset($p->loyaltyNumber) ? $p->loyaltyNumber : '(none)';

                    echo '    ' . ($index + 1) . '. ';
                    echo $first . ' ' . $last;
                    echo ' (' . ($p->type ?? '') . ')';
                    echo ', DOB: ' . $dob;
                    echo ', loyalty: ' . $loyalty . "\n";
                }
            }
        } else {
            echo "  No passengers element found in booking.\n";
        }

        echo "\n";
    } else {
        echo "No booking element found in response.\n\n";
    }

    $lastRequest  = $client->__getLastRequest();
    $lastResponse = $client->__getLastResponse();

    echo "=== LAST REQUEST (PurchaseTicket) ===\n";
    echo pretty_print_xml($lastRequest) . "\n";

    echo "=== LAST RESPONSE (PurchaseTicket) ===\n";
    echo pretty_print_xml($lastResponse) . "\n";

} catch (SoapFault $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n\n";

    if (isset($client)) {
        $lastRequest  = $client->__getLastRequest();
        $lastResponse = $client->__getLastResponse();

        echo "=== LAST REQUEST ===\n";
        echo $lastRequest !== null && $lastRequest !== ''
            ? pretty_print_xml($lastRequest) . "\n"
            : "(none)\n";

        echo "=== LAST RESPONSE ===\n";
        echo $lastResponse !== null && $lastResponse !== ''
            ? pretty_print_xml($lastResponse) . "\n"
            : "(none)\n";
    }
}
