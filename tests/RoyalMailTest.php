<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ParcelTrap\DTOs\TrackingDetails;
use ParcelTrap\Enums\Status;
use ParcelTrap\ParcelTrap;
use ParcelTrap\RoyalMail\RoyalMail;

it('can add the RoyalMail driver to ParcelTrap', function () {
    $client = ParcelTrap::make(['royal_mail' => RoyalMail::make(['client_id' => 'abcdefg', 'client_secret' => 'hijklmno', 'accept_terms' => true])]);
    $client->addDriver('royal_mail_other', RoyalMail::make(['client_id' => 'abcdefg', 'client_secret' => 'hijklmno', 'accept_terms' => true]));

    expect($client)->hasDriver('royal_mail')->toBeTrue();
    expect($client)->hasDriver('royal_mail_other')->toBeTrue();
});

it('can retrieve the RoyalMail driver from ParcelTrap', function () {
    expect(ParcelTrap::make(['royal_mail' => RoyalMail::make(['client_id' => 'abcdefg', 'client_secret' => 'hijklmno', 'accept_terms' => true])]))
        ->hasDriver('royal_mail')->toBeTrue()
        ->driver('royal_mail')->toBeInstanceOf(RoyalMail::class);
});

it('can call `find` on the RoyalMail driver', function () {
    $trackingDetails = [
         "mailPieceId" => "090367574000000FE1E1B", 
         "carrierShortName" => "RM", 
         "carrierFullName" => "Royal Mail Group Ltd", 
         "summary" => [
            "uniqueItemId" => "090367574000000FE1E1B", 
            "oneDBarcode" => "FQ087430672GB", 
            "productId" => "SD2", 
            "productName" => "Special Delivery Guaranteed", 
            "productDescription" => "Our guaranteed next working day service with tracking and a signature on delivery", 
            "productCategory" => "NON-INTERNATIONAL", 
            "destinationCountryCode" => "GBR", 
            "destinationCountryName" => "United Kingdom of Great Britain and Northern Ireland", 
            "originCountryCode" => "GBR", 
            "originCountryName" => "United Kingdom of Great Britain and Northern Ireland", 
            "lastEventCode" => "EVNMI", 
            "lastEventName" => "Forwarded - Mis-sort", 
            "lastEventDateTime" => "2016-10-20T10:04:00+01:00", 
            "lastEventLocationName" => "Stafford DO", 
            "statusDescription" => "It's being redirected", 
            "statusCategory" => "IN TRANSIT", 
            "statusHelpText" => "The item is in transit and a confirmation will be provided on delivery. For more information on levels of tracking by service, please see Sending Mail.", 
            "summaryLine" => "Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.", 
            "internationalPostalProvider" => [
               "url" => "https://www.royalmail.com/track-your-item", 
               "title" => "Royal Mail Group Ltd", 
               "description" => "Royal Mail Group Ltd" 
            ] 
         ], 
         "signature" => [
                  "recipientName" => "Simon", 
                  "signatureDateTime" => "2016-10-20T10:04:00+01:00", 
                  "imageId" => "001234" 
               ], 
         "estimatedDelivery" => [
                     "date" => "2017-02-20", 
                     "startOfEstimatedWindow" => "08:00:00+01:00", 
                     "endOfEstimatedWindow" => "11:00:00+01:00" 
                  ], 
         "events" => [
                        [
                           "eventCode" => "EVNMI", 
                           "eventName" => "Forwarded - Mis-sort", 
                           "eventDateTime" => "2016-10-20T10:04:00+01:00", 
                           "locationName" => "Stafford DO" 
                        ] 
                     ], 
         "links" => [
                              "summary" => [
                                 "href" => "/mailpieces/v2/summary?mailPieceId=090367574000000FE1E1B", 
                                 "title" => "Summary", 
                                 "description" => "Get summary" 
                              ], 
                              "signature" => [
                                    "href" => "/mailpieces/v2/090367574000000FE1E1B/signature", 
                                    "title" => "Signature", 
                                    "description" => "Get signature" 
                                 ], 
                              "redelivery" => [
                                       "href" => "/personal/receiving-mail/redelivery", 
                                       "title" => "Redelivery", 
                                       "description" => "Book a redelivery" 
                                    ] 
                           ] 
      ];

    $httpMockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode(['mailPieces' => $trackingDetails])),
    ]);

    $handlerStack = HandlerStack::create($httpMockHandler);

    $httpClient = new Client([
        'handler' => $handlerStack,
    ]);

    expect(ParcelTrap::make(['royal_mail' => RoyalMail::make(['client_id' => 'abcdefg', 'client_secret' => 'hijklmno', 'accept_terms' => true], $httpClient)])->driver('royal_mail')->find('090367574000000FE1E1B'))
        ->toBeInstanceOf(TrackingDetails::class)
        ->identifier->toBe('090367574000000FE1E1B')
        ->status->toBe(Status::In_Transit)
        ->status->description()->toBe('In Transit')
        ->summary->toBe('Item FQ087430672GB was forwarded to the Delivery Office on 2016-10-20.')
        ->estimatedDelivery->toEqual(new DateTimeImmutable('2017-02-20T00:00:00.000000+0000'))
        ->raw->toBe($trackingDetails);
});
