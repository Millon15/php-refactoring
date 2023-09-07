<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Millon\PhpRefactoring\Test\Integration\Service\ExchangeRates\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Millon\PhpRefactoring\Service\ExchangeRates\Client\Client as UnitUnderTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

final class ClientTest extends KernelTestCase
{
    protected HttpClient|MockObject $mockClient;
    protected SerializerInterface|MockObject $serializerMock;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mockClient = $this->createMock(HttpClient::class);
    }

    /** @return array<array<string, string> */
    public static function success(): array
    {
        $baseCurrency = 'EUR';

        return [
            [
                '$baseUrl' => 'http://api.example.com',
                '$baseCurrency' => $baseCurrency,
                '$body' => '{"success":true,"timestamp":1694069223,"base":"' . $baseCurrency . '","date":"2023-09-07","rates":{"AED":3.937809,"AFN":78.790548,"ALL":107.936045,"AMD":413.742649,"ANG":1.93505,"AOA":887.152326,"ARS":375.196429,"AUD":1.677381,"AWG":1.936822,"AZN":1.823747,"BAM":1.955565,"BBD":2.16792,"BDT":117.835864,"BGN":1.955836,"BHD":0.404195,"BIF":3040.092022,"BMD":1.072081,"BND":1.462052,"BOB":7.418972,"BRL":5.337568,"BSD":1.073761,"BTC":4.1585701e-5,"BTN":89.269026,"BWP":14.728233,"BYN":2.71015,"BYR":21012.795291,"BZD":2.16432,"CAD":1.461901,"CDF":2696.284074,"CHF":0.955685,"CLF":0.033905,"CLP":935.530683,"CNY":7.853531,"COP":4359.297358,"CRC":575.636316,"CUC":1.072081,"CUP":28.410157,"CVE":110.249718,"CZK":24.306551,"DJF":191.175283,"DKK":7.457237,"DOP":60.893263,"DZD":146.897646,"EGP":33.12817,"ERN":16.081221,"ETB":59.344635,"EUR":1,"FJD":2.436091,"FKP":0.852175,"GBP":0.857805,"GEL":2.808458,"GGP":0.852175,"GHS":12.240303,"GIP":0.852175,"GMD":64.860102,"GNF":9215.980408,"GTQ":8.449829,"GYD":224.638861,"HKD":8.40485,"HNL":26.445074,"HRK":7.378922,"HTG":146.565152,"HUF":390.25799,"IDR":16437.312721,"ILS":4.119838,"IMP":0.852175,"INR":89.171981,"IQD":1406.118205,"IRR":45311.520044,"ISK":143.916038,"JEP":0.852175,"JMD":166.040082,"JOD":0.759354,"JPY":158.153512,"KES":156.68432,"KGS":94.632662,"KHR":4461.548021,"KMF":492.245842,"KPW":964.924918,"KRW":1431.351935,"KWD":0.330715,"KYD":0.894784,"KZT":498.263257,"LAK":21239.15497,"LBP":16085.616956,"LKR":346.82516,"LRD":199.738193,"LSL":20.605775,"LTL":3.165578,"LVL":0.648491,"LYD":5.200328,"MAD":10.985777,"MDL":19.192429,"MGA":4784.381435,"MKD":61.613931,"MMK":2254.80848,"MNT":3724.448727,"MOP":8.672679,"MRO":382.732873,"MUR":48.399611,"MVR":16.569067,"MWK":1126.154401,"MXN":18.911184,"MYR":5.014104,"MZN":67.809095,"NAD":20.605627,"NGN":808.735461,"NIO":39.285654,"NOK":11.478862,"NPR":142.834198,"NZD":1.821115,"OMR":0.412745,"PAB":1.073651,"PEN":3.971024,"PGK":3.930565,"PHP":60.915468,"PKR":330.380986,"PLN":4.578721,"PYG":7797.991811,"QAR":3.903441,"RON":4.959873,"RSD":117.282455,"RUB":105.287504,"RWF":1297.044406,"SAR":4.02113,"SBD":9.054767,"SCR":13.87487,"SDG":644.469538,"SEK":11.905132,"SGD":1.462839,"SHP":1.304455,"SLE":23.996098,"SLL":21173.607594,"SOS":610.549144,"SSP":644.321288,"SRD":41.250426,"STD":22189.920299,"SYP":13938.666226,"SZL":20.647331,"THB":38.154835,"TJS":11.794565,"TMT":3.763006,"TND":3.355405,"TOP":2.568975,"TRY":28.754506,"TTD":7.272026,"TWD":34.327508,"TZS":2685.563411,"UAH":39.672571,"UGX":4004.44492,"USD":1.072081,"UYU":40.554379,"UZS":13024.43758,"VEF":3480750.956698,"VES":34.810429,"VND":25793.742261,"VUV":130.085035,"WST":2.941913,"XAF":655.872203,"XAG":0.046379,"XAU":0.000559,"XCD":2.897354,"XDR":0.807122,"XOF":655.872203,"XPF":119.698245,"YER":268.395378,"ZAR":20.579803,"ZMK":9650.052759,"ZMW":22.091144,"ZWL":345.209771}}',
            ],
        ];
    }

    #[DataProvider('success')]
    public function testSuccess(string $baseUrl, string $baseCurrency, string $body): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with("$baseUrl/latest")
            ->willReturn(new Response(200, body: $body));

        $client = new UnitUnderTest($baseUrl, $this->mockClient, $serializer);
        $rates = $client->latest();

        $this->assertEquals($baseCurrency, $rates->base);
        $this->assertNotEmpty($rates->rates);
        $this->assertEquals('1', $rates->rates[$rates->base]);
    }

    /** @return array<array<string, string> */
    public static function failure(): array
    {
        return [
            [
                '$baseUrl' => 'https://api.example.com',
            ],
        ];
    }

    #[DataProvider('failure')]
    public function testFailure(string $baseUrl): void
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        $this->mockClient->expects($this->once())
            ->method('get')
            ->with("$baseUrl/latest")
            ->willReturn(new Response(403));

        $client = new UnitUnderTest($baseUrl, $this->mockClient, $serializer);
        $this->expectException(NotEncodableValueException::class);
        $client->latest();
    }
}
