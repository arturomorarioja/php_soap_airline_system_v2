# Airline System v2
SOAP example. It simulates the search flights operation for an airline system. Functionalities
- Creation of the WSDL document `AirlineService.wsdl`
- Implementation of a SOAP server and client that handle the operations `SearchFlights` and `PurchaseTicket`

## Usage

### With Docker
1. Build the image: `docker compose build`
2. Generate the WSDL file: `docker compose run --rm cli generate_wsdl.php`. It will generate `BankAccountService.wsdl`
3. Start the SOAP server: `docker compose up server`. It will be reachable at `http://localhost:8080`
4. In a different terminal, run the client: `docker compose run --rm cli client.php`. It will show:
  - `SearchFlights`: the hardcoded flights, the XML request, and the XML response
  - `PurchaseTicket`:
    - Per ticket: number, status, issue date and time
    - Per booking: PNR, status, passengers
      - Per passenger: full name, passenger type, date of birth, loyalty card number
    
### As a PHP project
Preconditions:
- Make sure that the line `extension=soap` is uncommented in the configuration file `php.ini`

WSDL generation:
- Run `php generate_wsdl.php`. It will generate `AirlineService.wsdl`

Starting the SOAP server:
- Run `php -S 127.0.0.1:8080`. If a different IP or port is desired, edit it in `classes/Config.php`

Running the SOAP client:
- In a different terminal, execute the client request `SearchFlights`: `php client.php`. It will show:
  - `SearchFlights`: the hardcoded flights, the XML request, and the XML response
  - `PurchaseTicket`:
    - Per ticket: number, status, issue date and time
    - Per booking: PNR, status, passengers
      - Per passenger: full name, passenger type, date of birth, loyalty card number

## Tools
PHP8

## Author
ChatGPT 5.1, prompted by Arturo Mora-Rioja