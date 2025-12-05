<?php
// generate_wsdl.php

/**
 * generate_wsdl.php
 *
 * Generates AirlineService.wsdl with two operations: SearchFlights and PurchaseTicket.
 * No external libraries are used. Uses DOMDocument and namespaced nodes.
 *
 * Contract:
 * - Operations:
 *      - SearchFlights
 *      - PurchaseTicket
 *
 * Types (tns):
 * - IataCode: pattern [A-Z]{3}
 * - CabinClass: ECONOMY | PREMIUM_ECONOMY | BUSINESS | FIRST
 * - CurrencyCode: pattern [A-Z]{3}
 * - PaymentMethod: CARD | WALLET | BANK_TRANSFER
 * - TicketStatus: ISSUED | VOIDED | REFUNDED
 * - BookingStatus: ON_HOLD | TICKETED | CANCELLED
 * - PassengerType: ADT | CHD | INF
 *
 * - PassengerCounts:
 *      adultCount (xs:int), childCount (xs:int, minOccurs=0), infantCount (xs:int, minOccurs=0)
 *
 * - Money:
 *      amount (xs:decimal), currency (CurrencyCode)
 *
 * - Flight:
 *      carrier (xs:string), flightNumber (xs:string),
 *      departDateTime (xs:dateTime), arriveDateTime (xs:dateTime),
 *      origin (IataCode), destination (IataCode), price (Money)
 *
 * - Name:
 *      first (xs:string), last (xs:string)
 *
 * - PaymentCard:
 *      holderName (xs:string), number (xs:string),
 *      expiryMonth (xs:int), expiryYear (xs:int), cvv (xs:string)
 *
 * - PaymentInfo:
 *      method (PaymentMethod), card (PaymentCard, minOccurs=0),
 *      amount (xs:decimal), currency (CurrencyCode)
 *
 * - Passenger:
 *      type (PassengerType), name (Name),
 *      dob (xs:date, minOccurs=0), loyaltyNumber (xs:string, minOccurs=0)
 *
 * - Booking:
 *      pnr (xs:string), status (BookingStatus),
 *      passengers (Passenger, repeating list)
 *
 * - Ticket:
 *      ticketNumber (xs:string), status (TicketStatus), issuedAt (xs:dateTime)
 *
 * Elements:
 * - SearchFlightsRequest:
 *      origin (IataCode), destination (IataCode),
 *      departureDate (xs:date), returnDate (xs:date, minOccurs=0),
 *      passengers (PassengerCounts), cabin (CabinClass, minOccurs=0)
 *
 * - SearchFlightsResponse:
 *      flights (Flight, repeating list)
 *
 * - PurchaseTicketRequest:
 *      pnr (xs:string), payment (PaymentInfo), billingEmail (xs:string)
 *
 * - PurchaseTicketResponse:
 *      tickets (Ticket, repeating list), booking (Booking)
 */

declare(strict_types=1);

$NS = [
    'wsdl' => 'http://schemas.xmlsoap.org/wsdl/',
    'soap' => 'http://schemas.xmlsoap.org/wsdl/soap/',
    'xs'   => 'http://www.w3.org/2001/XMLSchema',
    'tns'  => 'http://example.com/air/booking'
];

$serviceName  = 'AirlineService';
$portTypeName = 'AirlinePortType';
$bindingName  = 'AirlineBinding';
$portName     = 'AirlinePort';
$soapAddress  = 'https://api.example.com/air/booking';

$soapActionSearchFlights  = $NS['tns'] . '/SearchFlights';
$soapActionPurchaseTicket = $NS['tns'] . '/PurchaseTicket';

$outputFile = __DIR__ . DIRECTORY_SEPARATOR . 'AirlineService.wsdl';

$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

/* Helper: create namespaced element with optional text and attributes */
$E = function (DOMNode $parent, string $ns, string $qname, ?string $text = null, array $attrs = []) use ($doc) : DOMElement {
    $el = $doc->createElementNS($ns, $qname);
    if ($text !== null) {
        $el->appendChild($doc->createTextNode($text));
    }
    foreach ($attrs as $k => $v) {
        $el->setAttribute($k, $v);
    }
    $parent->appendChild($el);
    return $el;
};

/* <wsdl:definitions> */
$definitions = $doc->createElementNS($NS['wsdl'], 'wsdl:definitions');
$definitions->setAttribute('xmlns:wsdl', $NS['wsdl']);
$definitions->setAttribute('xmlns:soap', $NS['soap']);
$definitions->setAttribute('xmlns:xs', $NS['xs']);
$definitions->setAttribute('xmlns:tns', $NS['tns']);
$definitions->setAttribute('targetNamespace', $NS['tns']);
$definitions->setAttribute('name', $serviceName);
$doc->appendChild($definitions);

/* <wsdl:types> with one <xs:schema> (targetNamespace=tns) */
$types  = $E($definitions, $NS['wsdl'], 'wsdl:types');
$schema = $E($types, $NS['xs'], 'xs:schema', null, [
    'targetNamespace'    => $NS['tns'],
    'elementFormDefault' => 'qualified'
]);

/* ===== Simple types ===== */

/* IataCode: pattern [A-Z]{3} */
$stIata = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'IataCode']);
$restIata = $E($stIata, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
$E($restIata, $NS['xs'], 'xs:pattern', null, ['value' => '[A-Z]{3}']);

/* CabinClass enum */
$stCabin = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'CabinClass']);
$restCabin = $E($stCabin, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
foreach (['ECONOMY', 'PREMIUM_ECONOMY', 'BUSINESS', 'FIRST'] as $val) {
    $E($restCabin, $NS['xs'], 'xs:enumeration', null, ['value' => $val]);
}

/* CurrencyCode: pattern [A-Z]{3} */
$stCurrency = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'CurrencyCode']);
$restCurrency = $E($stCurrency, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
$E($restCurrency, $NS['xs'], 'xs:pattern', null, ['value' => '[A-Z]{3}']);

/* PaymentMethod enum */
$stPaymentMethod = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'PaymentMethod']);
$restPaymentMethod = $E($stPaymentMethod, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
foreach (['CARD', 'WALLET', 'BANK_TRANSFER'] as $val) {
    $E($restPaymentMethod, $NS['xs'], 'xs:enumeration', null, ['value' => $val]);
}

/* TicketStatus enum */
$stTicketStatus = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'TicketStatus']);
$restTicketStatus = $E($stTicketStatus, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
foreach (['ISSUED', 'VOIDED', 'REFUNDED'] as $val) {
    $E($restTicketStatus, $NS['xs'], 'xs:enumeration', null, ['value' => $val]);
}

/* BookingStatus enum */
$stBookingStatus = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'BookingStatus']);
$restBookingStatus = $E($stBookingStatus, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
foreach (['ON_HOLD', 'TICKETED', 'CANCELLED'] as $val) {
    $E($restBookingStatus, $NS['xs'], 'xs:enumeration', null, ['value' => $val]);
}

/* PassengerType enum */
$stPassengerType = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'PassengerType']);
$restPassengerType = $E($stPassengerType, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
foreach (['ADT', 'CHD', 'INF'] as $val) {
    $E($restPassengerType, $NS['xs'], 'xs:enumeration', null, ['value' => $val]);
}

/* ===== Complex types ===== */

/* PassengerCounts */
$ctPax = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'PassengerCounts']);
$seqPax = $E($ctPax, $NS['xs'], 'xs:sequence');
$E($seqPax, $NS['xs'], 'xs:element', null, ['name' => 'adultCount',  'type' => 'xs:int']);
$E($seqPax, $NS['xs'], 'xs:element', null, ['name' => 'childCount',  'type' => 'xs:int', 'minOccurs' => '0']);
$E($seqPax, $NS['xs'], 'xs:element', null, ['name' => 'infantCount', 'type' => 'xs:int', 'minOccurs' => '0']);

/* Money */
$ctMoney = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Money']);
$seqMoney = $E($ctMoney, $NS['xs'], 'xs:sequence');
$E($seqMoney, $NS['xs'], 'xs:element', null, ['name' => 'amount',   'type' => 'xs:decimal']);
$E($seqMoney, $NS['xs'], 'xs:element', null, ['name' => 'currency', 'type' => 'tns:CurrencyCode']);

/* Flight */
$ctFlight = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Flight']);
$seqFlight = $E($ctFlight, $NS['xs'], 'xs:sequence');
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'carrier',         'type' => 'xs:string']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'flightNumber',    'type' => 'xs:string']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'departDateTime',  'type' => 'xs:dateTime']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'arriveDateTime',  'type' => 'xs:dateTime']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'origin',          'type' => 'tns:IataCode']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'destination',     'type' => 'tns:IataCode']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'price',           'type' => 'tns:Money']);

/* Name */
$ctName = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Name']);
$seqName = $E($ctName, $NS['xs'], 'xs:sequence');
$E($seqName, $NS['xs'], 'xs:element', null, ['name' => 'first', 'type' => 'xs:string']);
$E($seqName, $NS['xs'], 'xs:element', null, ['name' => 'last',  'type' => 'xs:string']);

/* PaymentCard */
$ctPaymentCard = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'PaymentCard']);
$seqPaymentCard = $E($ctPaymentCard, $NS['xs'], 'xs:sequence');
$E($seqPaymentCard, $NS['xs'], 'xs:element', null, ['name' => 'holderName',  'type' => 'xs:string']);
$E($seqPaymentCard, $NS['xs'], 'xs:element', null, ['name' => 'number',      'type' => 'xs:string']);
$E($seqPaymentCard, $NS['xs'], 'xs:element', null, ['name' => 'expiryMonth', 'type' => 'xs:int']);
$E($seqPaymentCard, $NS['xs'], 'xs:element', null, ['name' => 'expiryYear',  'type' => 'xs:int']);
$E($seqPaymentCard, $NS['xs'], 'xs:element', null, ['name' => 'cvv',         'type' => 'xs:string']);

/* PaymentInfo */
$ctPaymentInfo = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'PaymentInfo']);
$seqPaymentInfo = $E($ctPaymentInfo, $NS['xs'], 'xs:sequence');
$E($seqPaymentInfo, $NS['xs'], 'xs:element', null, ['name' => 'method',   'type' => 'tns:PaymentMethod']);
$E($seqPaymentInfo, $NS['xs'], 'xs:element', null, ['name' => 'card',     'type' => 'tns:PaymentCard', 'minOccurs' => '0']);
$E($seqPaymentInfo, $NS['xs'], 'xs:element', null, ['name' => 'amount',   'type' => 'xs:decimal']);
$E($seqPaymentInfo, $NS['xs'], 'xs:element', null, ['name' => 'currency', 'type' => 'tns:CurrencyCode']);

/* Passenger */
$ctPassenger = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Passenger']);
$seqPassenger = $E($ctPassenger, $NS['xs'], 'xs:sequence');
$E($seqPassenger, $NS['xs'], 'xs:element', null, ['name' => 'type',          'type' => 'tns:PassengerType']);
$E($seqPassenger, $NS['xs'], 'xs:element', null, ['name' => 'name',          'type' => 'tns:Name']);
$E($seqPassenger, $NS['xs'], 'xs:element', null, ['name' => 'dob',           'type' => 'xs:date', 'minOccurs' => '0']);
$E($seqPassenger, $NS['xs'], 'xs:element', null, ['name' => 'loyaltyNumber', 'type' => 'xs:string', 'minOccurs' => '0']);

/* Booking */
$ctBooking = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Booking']);
$seqBooking = $E($ctBooking, $NS['xs'], 'xs:sequence');
$E($seqBooking, $NS['xs'], 'xs:element', null, ['name' => 'pnr',    'type' => 'xs:string']);
$E($seqBooking, $NS['xs'], 'xs:element', null, ['name' => 'status', 'type' => 'tns:BookingStatus']);
$E($seqBooking, $NS['xs'], 'xs:element', null, [
    'name'      => 'passengers',
    'type'      => 'tns:Passenger',
    'minOccurs' => '0',
    'maxOccurs' => 'unbounded'
]);

/* Ticket */
$ctTicket = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Ticket']);
$seqTicket = $E($ctTicket, $NS['xs'], 'xs:sequence');
$E($seqTicket, $NS['xs'], 'xs:element', null, ['name' => 'ticketNumber', 'type' => 'xs:string']);
$E($seqTicket, $NS['xs'], 'xs:element', null, ['name' => 'status',       'type' => 'tns:TicketStatus']);
$E($seqTicket, $NS['xs'], 'xs:element', null, ['name' => 'issuedAt',     'type' => 'xs:dateTime']);

/* ===== Elements ===== */

/* SearchFlightsRequest element (wrapped) */
$elReq = $E($schema, $NS['xs'], 'xs:element', null, ['name' => 'SearchFlightsRequest']);
$ctReq = $E($elReq, $NS['xs'], 'xs:complexType');
$seqReq = $E($ctReq, $NS['xs'], 'xs:sequence');
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'origin',        'type' => 'tns:IataCode']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'destination',   'type' => 'tns:IataCode']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'departureDate', 'type' => 'xs:date']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'returnDate',    'type' => 'xs:date', 'minOccurs' => '0']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'passengers',    'type' => 'tns:PassengerCounts']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'cabin',         'type' => 'tns:CabinClass', 'minOccurs' => '0']);

/* SearchFlightsResponse element (wrapped) */
$elRes = $E($schema, $NS['xs'], 'xs:element', null, ['name' => 'SearchFlightsResponse']);
$ctRes = $E($elRes, $NS['xs'], 'xs:complexType');
$seqRes = $E($ctRes, $NS['xs'], 'xs:sequence');
$E($seqRes, $NS['xs'], 'xs:element', null, [
    'name'      => 'flights',
    'type'      => 'tns:Flight',
    'minOccurs' => '0',
    'maxOccurs' => 'unbounded'
]);

/* PurchaseTicketRequest element (wrapped) */
$elPTReq = $E($schema, $NS['xs'], 'xs:element', null, ['name' => 'PurchaseTicketRequest']);
$ctPTReq = $E($elPTReq, $NS['xs'], 'xs:complexType');
$seqPTReq = $E($ctPTReq, $NS['xs'], 'xs:sequence');
$E($seqPTReq, $NS['xs'], 'xs:element', null, ['name' => 'pnr',          'type' => 'xs:string']);
$E($seqPTReq, $NS['xs'], 'xs:element', null, ['name' => 'payment',      'type' => 'tns:PaymentInfo']);
$E($seqPTReq, $NS['xs'], 'xs:element', null, ['name' => 'billingEmail', 'type' => 'xs:string']);

/* PurchaseTicketResponse element (wrapped) */
$elPTRes = $E($schema, $NS['xs'], 'xs:element', null, ['name' => 'PurchaseTicketResponse']);
$ctPTRes = $E($elPTRes, $NS['xs'], 'xs:complexType');
$seqPTRes = $E($ctPTRes, $NS['xs'], 'xs:sequence');
$E($seqPTRes, $NS['xs'], 'xs:element', null, [
    'name'      => 'tickets',
    'type'      => 'tns:Ticket',
    'minOccurs' => '0',
    'maxOccurs' => 'unbounded'
]);
$E($seqPTRes, $NS['xs'], 'xs:element', null, ['name' => 'booking', 'type' => 'tns:Booking']);

/* ===== WSDL messages ===== */
$msgIn  = $E($definitions, $NS['wsdl'], 'wsdl:message', null, ['name' => 'SearchFlightsInput']);
$E($msgIn, $NS['wsdl'], 'wsdl:part', null, ['name' => 'parameters', 'element' => 'tns:SearchFlightsRequest']);

$msgOut = $E($definitions, $NS['wsdl'], 'wsdl:message', null, ['name' => 'SearchFlightsOutput']);
$E($msgOut, $NS['wsdl'], 'wsdl:part', null, ['name' => 'parameters', 'element' => 'tns:SearchFlightsResponse']);

$msgPTIn = $E($definitions, $NS['wsdl'], 'wsdl:message', null, ['name' => 'PurchaseTicketInput']);
$E($msgPTIn, $NS['wsdl'], 'wsdl:part', null, ['name' => 'parameters', 'element' => 'tns:PurchaseTicketRequest']);

$msgPTOut = $E($definitions, $NS['wsdl'], 'wsdl:message', null, ['name' => 'PurchaseTicketOutput']);
$E($msgPTOut, $NS['wsdl'], 'wsdl:part', null, ['name' => 'parameters', 'element' => 'tns:PurchaseTicketResponse']);

/* ===== WSDL portType ===== */
$pt = $E($definitions, $NS['wsdl'], 'wsdl:portType', null, ['name' => $portTypeName]);

$opSearch = $E($pt, $NS['wsdl'], 'wsdl:operation', null, ['name' => 'SearchFlights']);
$E($opSearch, $NS['wsdl'], 'wsdl:input', null, ['message' => 'tns:SearchFlightsInput']);
$E($opSearch, $NS['wsdl'], 'wsdl:output', null, ['message' => 'tns:SearchFlightsOutput']);

$opPT = $E($pt, $NS['wsdl'], 'wsdl:operation', null, ['name' => 'PurchaseTicket']);
$E($opPT, $NS['wsdl'], 'wsdl:input', null, ['message' => 'tns:PurchaseTicketInput']);
$E($opPT, $NS['wsdl'], 'wsdl:output', null, ['message' => 'tns:PurchaseTicketOutput']);

/* ===== WSDL binding (SOAP document/literal) ===== */
$binding = $E($definitions, $NS['wsdl'], 'wsdl:binding', null, [
    'name' => $bindingName,
    'type' => 'tns:' . $portTypeName
]);
$E($binding, $NS['soap'], 'soap:binding', null, [
    'style'     => 'document',
    'transport' => 'http://schemas.xmlsoap.org/soap/http'
]);

$opbSearch = $E($binding, $NS['wsdl'], 'wsdl:operation', null, ['name' => 'SearchFlights']);
$E($opbSearch, $NS['soap'], 'soap:operation', null, ['soapAction' => $soapActionSearchFlights]);
$inbSearch = $E($opbSearch, $NS['wsdl'], 'wsdl:input');
$E($inbSearch, $NS['soap'], 'soap:body', null, ['use' => 'literal']);
$outbSearch = $E($opbSearch, $NS['wsdl'], 'wsdl:output');
$E($outbSearch, $NS['soap'], 'soap:body', null, ['use' => 'literal']);

$opbPT = $E($binding, $NS['wsdl'], 'wsdl:operation', null, ['name' => 'PurchaseTicket']);
$E($opbPT, $NS['soap'], 'soap:operation', null, ['soapAction' => $soapActionPurchaseTicket]);
$inbPT = $E($opbPT, $NS['wsdl'], 'wsdl:input');
$E($inbPT, $NS['soap'], 'soap:body', null, ['use' => 'literal']);
$outbPT = $E($opbPT, $NS['wsdl'], 'wsdl:output');
$E($outbPT, $NS['soap'], 'soap:body', null, ['use' => 'literal']);

/* ===== WSDL service / port ===== */
$service = $E($definitions, $NS['wsdl'], 'wsdl:service', null, ['name' => $serviceName]);
$port = $E($service, $NS['wsdl'], 'wsdl:port', null, [
    'name'    => $portName,
    'binding' => 'tns:' . $bindingName
]);
$E($port, $NS['soap'], 'soap:address', null, ['location' => $soapAddress]);

/* Write file */
if ($doc->save($outputFile) === false) {
    fwrite(STDERR, "Failed to write WSDL file.\n");
    exit(1);
}
echo "WSDL generated: {$outputFile}\n";
