<?php
// AirlineService.php

declare(strict_types=1);

/**
 * AirlineService
 *
 * Implements the operations defined in AirlineService.wsdl:
 * - SearchFlights
 * - PurchaseTicket
 */
class AirlineService
{
    /**
     * SearchFlights operation.
     *
     * @param stdClass $request
     * @return array
     */
    public function SearchFlights($request): array
    {
        $origin        = isset($request->origin) ? (string)$request->origin : '';
        $destination   = isset($request->destination) ? (string)$request->destination : '';
        $departureDate = isset($request->departureDate) ? (string)$request->departureDate : '';
        $returnDate    = isset($request->returnDate) ? (string)$request->returnDate : null;
        $cabin         = isset($request->cabin) ? (string)$request->cabin : 'ECONOMY';

        $adultCount  = isset($request->passengers->adultCount) ? (int)$request->passengers->adultCount : 1;
        $childCount  = isset($request->passengers->childCount) ? (int)$request->passengers->childCount : 0;
        $infantCount = isset($request->passengers->infantCount) ? (int)$request->passengers->infantCount : 0;

        if ($origin === '' || $destination === '' || $departureDate === '') {
            return [
                'flights' => [],
            ];
        }

        $flights = [];

        $buildDateTime = function (string $date, string $time) {
            return $date . 'T' . $time;
        };

        $flights[] = [
            'carrier'        => 'Demo Air',
            'flightNumber'   => 'DA123',
            'departDateTime' => $buildDateTime($departureDate, '09:00:00'),
            'arriveDateTime' => $buildDateTime($departureDate, '12:30:00'),
            'origin'         => $origin,
            'destination'    => $destination,
            'price'          => [
                'amount'   => 199.99,
                'currency' => 'EUR',
            ],
        ];

        if (!empty($returnDate)) {
            $flights[] = [
                'carrier'        => 'Demo Air',
                'flightNumber'   => 'DA124',
                'departDateTime' => $buildDateTime($returnDate, '17:00:00'),
                'arriveDateTime' => $buildDateTime($returnDate, '20:30:00'),
                'origin'         => $destination,
                'destination'    => $origin,
                'price'          => [
                    'amount'   => 219.50,
                    'currency' => 'EUR',
                ],
            ];
        }

        return [
            'flights' => $flights,
        ];
    }

    /**
     * PurchaseTicket operation.
     *
     * @param stdClass $request
     * @return array
     */
    public function PurchaseTicket($request): array
    {
        $pnr = isset($request->pnr) ? (string)$request->pnr : 'UNKNOWN';

        $now = date('c');

        $passengers = [];

        $passengers[] = [
            'type' => 'ADT',
            'name' => [
                'first' => 'John',
                'last'  => 'Doe',
            ],
            'dob'           => '1980-01-15',
            'loyaltyNumber' => 'AB123456',
        ];

        $passengers[] = [
            'type' => 'CHD',
            'name' => [
                'first' => 'Jane',
                'last'  => 'Doe',
            ],
            'dob' => '2015-06-30',
        ];

        $tickets = [];
        foreach ($passengers as $index => $pax) {
            $tickets[] = [
                'ticketNumber' => sprintf('220-%010d', $index + 1),
                'status'       => 'ISSUED',
                'issuedAt'     => $now,
            ];
        }

        $booking = [
            'pnr'        => $pnr,
            'status'     => 'TICKETED',
            'passengers' => $passengers,
        ];

        return [
            'tickets' => $tickets,
            'booking' => $booking,
        ];
    }
}
