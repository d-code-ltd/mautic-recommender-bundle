<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\BuildJsEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Mautic\PluginBundle\Helper\IntegrationHelper;

/**
 * Class BuildJsSubscriber.
 */
class BuildJsSubscriber extends CommonSubscriber
{

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * BuildJsSubscriber constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(
        CoreParametersHelper $coreParametersHelper, 
        IntegrationHelper $integrationHelper
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper = $integrationHelper;    
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::BUILD_MAUTIC_JS => [
                ['onBuildJsTop', 300],
            ],
        ];
    }

    /**
     * @param BuildJsEvent $event
     */
    public function onBuildJsTop(BuildJsEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        
        $url = $this->router->generate('mautic_recommender_send_event', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $eventLabel = $this->coreParametersHelper->getParameter('eventLabel');
        //basic js
        $js = <<<JS
        
       MauticJS.recommenderEvent = function (params) {
        parms = {};
        var eventParams = {};
        
          if (typeof MauticJS.getInput === 'function') {
                queue = MauticJS.getInput('send', '{$eventLabel}');
            } else {
                return false;
            }
            if (queue) {
                for (var i=0; i<queue.length; i++) {
                    var event = queue[i];
                    // Merge user defined tracking pixel parameters.
                    if (typeof event[2] === 'object') {
                        for (var attr in event[2]) {
                            eventParams[attr] = event[2][attr];
                        }
                        parms['eventDetail'] = btoa(JSON.stringify(eventParams));
                        parms['params'] = btoa(unescape(encodeURIComponent(JSON.stringify(params.params))));
                    }
                    MauticJS.makeCORSRequest('POST', '{$url}', parms, 
                        function(response) {
                        },
                        function() {
                            
                    });
        
       }
                  }
            }
            
              // Process pageviews after new are added
    document.addEventListener('eventAddedToMauticQueue', function(e) {
      if(e.detail[0] == 'send' && e.detail[1] == '{$eventLabel}'){
         MauticJS.recommenderEvent();
      }
    });
       
       MauticJS.onFirstEventDelivery(MauticJS.recommenderEvent);
JS;
        $event->appendJs($js, 'RecommenderTemplate');
    }


}
