<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\NewItem;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class Event
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    // フロント：商品詳細画面に関連商品を表示
    public function newItem(FilterResponseEvent $event)
    {
      $app = $this->app;

      $newItemList = $app['orm.em']->getRepository('\Eccube\Entity\Product')
            ->findBy(
                array(),
                array('id' => 'DESC')
            );


        if (count($newItemList) > 0) {
            $twig = $app->renderView(
                'NewItem/Resource/template/new_item.twig',
                array(
                    'newItemList' => $newItemList,
                )
            );

            $response = $event->getResponse();

            $html = $response->getContent();

            $crawler = new Crawler($html);

            $oldElement = $crawler
                ->filter('#item_list');

            $oldHtml = $oldElement->html();
            $newHtml = $oldHtml.$twig;

            $html = $crawler->html();
            $html = str_replace($oldHtml, $newHtml, $html);

            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

            $first = array("<head>", "</body>");
            $last = array("<html lang=\"ja\"><head>", "</body></html>");
            $html = str_replace($first, $last, $html);
            
            $response->setContent($html);
            $event->setResponse($response);
        }
    }

}
