<?php

namespace Kanboard\Job;

use Kanboard\Event\GenericEvent;

/**
 * Class NotificationJob
 *
 * @package Kanboard\Job
 * @author  Frederic Guillot
 */
class NotificationJob extends BaseJob
{
    /**
     * Set job parameters
     *
     * @param GenericEvent $event
     * @param string       $eventName
     * @param string       $eventObjectName
     * @return $this
     */
    public function withParams(GenericEvent $event, $eventName, $eventObjectName)
    {
        $this->jobParams = array($event->getAll(), $eventName, $eventObjectName);
        return $this;
    }

    /**
     * Execute job
     *
     * @param array  $event
     * @param string $eventName
     * @param string $eventObjectName
     */
    public function execute(array $event, $eventName, $eventObjectName)
    {
        $eventData = $this->getEventData($event, $eventObjectName);

        if (! empty($eventData)) {
            if (! empty($event['mention'])) {
                $this->userNotificationModel->sendUserNotification($event['mention'], $eventName, $eventData);
            } else {
                $this->userNotificationModel->sendNotifications($eventName, $eventData);
                $this->projectNotificationModel->sendNotifications($eventData['task']['project_id'], $eventName, $eventData);
            }
        }
    }

    /**
     * Get event data
     *
     * @param array  $event
     * @param string $eventObjectName
     * @return array
     */
    public function getEventData(array $event, $eventObjectName)
    {
        $values = array();

        if (! empty($event['changes'])) {
            $values['changes'] = $event['changes'];
        }

        switch ($eventObjectName) {
            case 'Kanboard\Event\TaskEvent':
                $values['task'] = $this->taskFinderModel->getDetails($event['task_id']);
                break;
            default:
                $values = $event;
        }

        return $values;
    }
}
