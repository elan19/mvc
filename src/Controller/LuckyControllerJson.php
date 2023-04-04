<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

date_default_timezone_set('CET');

class LuckyControllerJson
{
    #[Route("/api/lucky/number")]
    public function jsonNumber(): Response
    {
        $number = random_int(0, 100);

        $data = [
            'lucky-number' => $number,
            'lucky-message' => 'Hi there!',
        ];

        /*$response = new Response();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;*/

        //return new JsonResponse($data);

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/quote")]
    public function jsonQuote(): Response
    {
        $quotes = [
                '0' => "Just have a little faith.",
                '1' => "I demand a trial by combat!",
                '2' => "If I die, turn my tweets in to a book.", 
                '3' => "Bazinga.",
                '4' => "I'm Pickle Rick.",
        ];

        $sources = [
            "0" => "Prison Break",
            "1" => "Game of Thrones",
            "2" => "Brooklyn Nine-Nine",
            "3" => "The Big Bang Theory",
            '4' => "Rick and Morty",
        ];

        $number = random_int(0, count($quotes)-1);

        $random_quote = $quotes[$number];
        $random_source = $sources[$number];
        $date = date('Y-m-d - H:i:s');

        $data = [
            'quote' => $random_quote,
            'source' => $random_source,
            'date' => $date,
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }
}
