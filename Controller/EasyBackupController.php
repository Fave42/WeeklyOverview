<?php

/*
 * This file is part of the EasyBackupBundle.
 * All rights reserved by Maximilian Groß (www.maximiliangross.de).
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\EasyBackupBundle\Controller;

use App\Controller\AbstractController;
use App\Repository\TimesheetRepository;

use App\Reporting\WeekByUser\WeekByUser;
use App\Model\DailyStatistic;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[IsGranted('easy_backup')]
#[Route('/admin/easy-backup')]
final class EasyBackupController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    private function formatDuration($seconds) {
        $negative = $seconds < 0;
        $seconds = abs($seconds);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        $formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        return $negative ? '-' . $formatted : $formatted;
    }

    /**
     * @return Response
     */
    #[Route('/', name: 'easy_backup', methods: ['GET', 'POST'])]
    public function indexAction(TimesheetRepository $timesheetRepository): Response
    {   

        $user = $this->getUser(); // Get the currently logged-in user

        $pageTitle = "This is the weekly report for $user";

        // Ensure there's a user object (i.e., the user is logged in)
        if (!$user) {
            throw $this->createNotFoundException('Not logged in');
        }

        // Fetch timesheets for the logged-in user
        $timesheet = $timesheetRepository->findBy(['user' => $user],['begin'=>'ASC']);

        
        $dateTimeFactory = $this->getDateTimeFactory($user);

        $values = new WeekByUser();
        $values->setUser($user);
        $values->setDate($dateTimeFactory->getStartOfWeek());
 
        $start = $dateTimeFactory->getStartOfWeek($values->getDate());
        $end = $dateTimeFactory->getEndOfWeek($values->getDate());

        // maybe do the calculating here
        $last_week = "9999-00";
        $week_list = [];
        $week_dict = array(
            'weeknumber'=>'',
            'totalworktimes'=> 0,
            'Monday'=> 0,
            'Tuesday'=> 0,
            'Wednesday'=> 0,
            'Thursday'=> 0,
            'Friday'=> 0,
            'Saturday'=> 0,
            'Sunday'=> 0,
        );
        foreach ($timesheet as $timesheet_entry) {
            $current_week = $timesheet_entry->getBegin()->format('Y-W');

            $this->logger->debug('##############');
            // $this->logger->debug('{last_week}', ['last_week' => $last_week]);
            // $this->logger->debug('{current_week}', ['current_week' => $current_week]);
            // $this->logger->debug('{begin}', ['begin' => $timesheet_entry->getBegin()]);
            // $this->logger->debug('{end}', ['end' => $timesheet_entry->getEnd()]);
            // $this->logger->debug('{day}', ['day' => $timesheet_entry->getBegin()->format('l')]);
            // $this->logger->debug('{duration}', ['duration' => $timesheet_entry->getCalculatedDuration()]);
            $this->logger->debug('{getWorkHoursMonday}', ['getWorkHoursMonday' => $user->getWorkHoursMonday()]);

            if ($current_week > $last_week){
                $week_dict['weeknumber'] = $last_week;
                
                $seconds = $week_dict['totalworktimes'];
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);

                array_push($week_list, $week_dict);

                $week_dict = array(
                    'weeknumber'=>'',
                    'totalworktimes'=> 0,
                    'Monday'=> 0,
                    'Tuesday'=> 0,
                    'Wednesday'=> 0,
                    'Thursday'=> 0,
                    'Friday'=> 0,
                    'Saturday'=> 0,
                    'Sunday'=> 0,
                );
                }
                $new_key = $timesheet_entry->getBegin()->format('l');
                $seconds = $timesheet_entry->getCalculatedDuration();

                // $this->logger->debug('{new_key}', ['new_key' => $new_key]);
                // $this->logger->debug('{new_value}', ['new_value' => $seconds]);
                
                if (array_key_exists($new_key, $week_dict)){
                    $old_seconds = $week_dict[$new_key];
                    // $this->logger->debug('{old_seconds}', ['old_seconds' => $old_seconds]);
                    // $this->logger->debug('{seconds}', ['seconds' => $seconds]);
                    $new_value = $old_seconds + $seconds;
                    $week_dict[$new_key] = $new_value;
                } else {
                    $week_dict[$new_key] = $seconds;
                }
                $week_dict['totalworktimes'] += $seconds;

            $last_week = $current_week;
        };
        

        foreach ($week_list as $item) {
            $item['Monday'] = $this->formatDuration($item['Monday']);
            $item['Tuesday'] = $this->formatDuration($item['Tuesday']);
            $item['Wednesday'] = $this->formatDuration($item['Wednesday']);
            $item['Thursday'] = $this->formatDuration($item['Thursday']);
            $item['Friday'] = $this->formatDuration($item['Friday']);
            $item['Saturday'] = $this->formatDuration($item['Saturday']);
            $item['Sunday'] = $this->formatDuration($item['Sunday']);
        }


        return $this->render('@EasyBackup/index.html.twig', [
            'user' => $user,
            'pageTitle' => $pageTitle,
            'period' => new DailyStatistic($start, $end, $user),
            'timesheets_weekly' => $timesheet,
            'week_list' => $week_list,
        ]);
    }
}
