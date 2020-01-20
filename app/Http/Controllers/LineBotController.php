<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Googlebook;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineBotController extends Controller
{
    public function books(Request $request)
    {
        Log::debug($request->header());
        Log::debug($request->input());

        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $signature = $request->header('x-line-signature');

        if (!$lineBot->validateSignature($request->getContent(), $signature)) {
            abort(400, 'Invalid signature');
        }

        $events = $lineBot->parseEventRequest($request->getContent(), $signature);

        Log::debug($events);

        foreach ($events as $event) {
            if (!($event instanceof TextMessage)) {
                Log::debug('Non text message has come');
                continue;
            }

        $googlebook = new Googlebook();
        $googlebookResponse = $googlebook->searchBooks($event->getText());

        if (array_key_exists('error', $googlebookResponse)) {
            $replyText = $googlebookResponse['error'][0]['message'];
            $replyToken = $event->getReplyToken();
            $lineBot->replyText($replyToken, $replyText);
            continue;
           }

        $replyText = '';
        foreach($googlebookResponse['items'] as $item) {
           $replyText .=
               $item['volumeInfo']['title']. "\n" .
               "\n";
          }

        $replyToken = $event->getReplyToken();
       $lineBot->replyText($replyToken, $replyText);

      }
    }
}
