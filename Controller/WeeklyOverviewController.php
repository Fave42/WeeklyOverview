<?php

/*
 * This file is part of the WeeklyOverviewBundle.
 * All rights reserved by Maximilian Groß (www.maximiliangross.de).
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\WeeklyOverviewBundle\Controller;

use App\Controller\AbstractController;
use App\Repository\TimesheetRepository;

use App\Reporting\WeekByUser\WeekByUser;
use App\Model\DailyStatistic;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[IsGranted('weekly_overview')]
#[Route('/admin/weekly-overview')]
final class WeeklyOverviewController extends AbstractController
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
    #[Route('/', name: 'weekly_overview', methods: ['GET', 'POST'])]
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

        
        // $dateTimeFactory = $this->getDateTimeFactory($user);

        // $values = new WeekByUser();
        // $values->setUser($user);
        // $values->setDate($dateTimeFactory->getStartOfWeek());

        // $week_start_date = $dateTimeFactory->getStartOfWeek($values->getDate())->format('Ymd');;
        // $week_end_date = $dateTimeFactory->getEndOfWeek($values->getDate())->format('Ymd');;

        // $this->logger->debug('{week_start_date}', ['week_start_date' => $week_start_date]);
        // $this->logger->debug('{week_end_date}', ['week_end_date' => $week_end_date]);

        $last_week = "0000-00";  // This means that we get a problem in the year 9999 ;)
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
        $lastDateIndex = count($timesheet) - 1;

        // Each timesheet_entry is a day
        foreach ($timesheet as $index => $timesheet_entry) {
            // $this->logger->debug('##############');
            
            $current_week = $timesheet_entry->getBegin()->format('o-W');

            $tmp_week = $timesheet_entry->getBegin()->format('W');
            $tmp_year = $timesheet_entry->getBegin()->format('o');
            // $this->logger->debug('{tmp_week}', ['tmp_week' => $tmp_week]);
            // $this->logger->debug('{tmp_year}', ['tmp_year' => $tmp_year]);

            if ($last_week == "0000-00") {
                $last_week = $current_week;
            };

            $current_date = $timesheet_entry->getBegin()->format('Ymd');

            if (
                ($current_week > $last_week)
                // ($index == $lastDateIndex) or
                // ($current_day_name === "Sunday")
                // ($last_week != "0000-00")
                // ( preg_match(pattern: "/.*?-01/i", subject: $current_day_name))
            )
            {
                // $this->logger->debug('#### We are in this stupid if ####');
                // if ($last_week == "9999-00") {
                //     $week_dict['weeknumber'] = $current_week;
                // } else {
                //     $week_dict['weeknumber'] = $last_week;
                // };
                
                $week_dict['weeknumber'] = $last_week;
                
                // Only save if there was work done
                if ($week_dict['totalworktimes'] != 0) {
                    array_push($week_list, $week_dict);
                }

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

                $last_week = $current_week;
            }

            // if ($index === 0) {
            //     $last_week = $current_week - 1;
            // }
            // $this->logger->debug('{last_week}', ['last_week' => $last_week]);
            // $this->logger->debug('{current_week}', ['current_week' => $current_week]);
            // $this->logger->debug('{begin}', ['begin' => $timesheet_entry->getBegin()]);
            // $this->logger->debug('{end}', ['end' => $timesheet_entry->getEnd()]);
            // $this->logger->debug('{day}', ['day' => $timesheet_entry->getBegin()->format('l')]);
            // $this->logger->debug('{duration}', ['duration' => $timesheet_entry->getCalculatedDuration()]);
            // $this->logger->debug('{getWorkHoursMonday}', ['getWorkHoursMonday' => $user->getWorkHoursMonday()]);

            $current_day_name = $timesheet_entry->getBegin()->format('l');
            $seconds = $timesheet_entry->getCalculatedDuration();
            
            // $this->logger->debug('{timesheet_entry}', ['timesheet_entry' => $timesheet_entry]);
            // $this->logger->debug('{current_day}', ['current_day' => $current_day_name]);
            // $this->logger->debug('{current_date}', ['current_date' => $current_date]);
            // $this->logger->debug('{seconds}', ['seconds' => $seconds]);
            $activity = $timesheet_entry->getActivity()->getName();
            // $this->logger->debug('{current_week}', ['current_week:' => $current_week], ['day:' => $current_day_name], ['seconds:' => $seconds]);

            // $this->logger->debug(sprintf(
            //     "current_week: %s   last_week: %s   day: %s   seconds: %s",
            //     $current_week, $last_week, $current_day_name, $seconds
            // ));
            // $this->logger->debug('{last_week}', ['last_week' => $last_week]);

            // if (array_key_exists($current_day_name, $week_dict)){
            // $old_seconds = $week_dict[$current_day_name];
            // $this->logger->debug(sprintf(
            //     "week_dict before: %s   old_seconds: %s   seconds: %s",
            //     $week_dict[$current_day_name], $old_seconds, $seconds
            // ));
            
            if ($activity === "Pause") {
                // $seconds = $old_seconds - $seconds;
                $week_dict[$current_day_name] -= $seconds;
                $week_dict['totalworktimes'] -= $seconds;
            } else {
                // $seconds = $old_seconds + $seconds;
                $week_dict[$current_day_name] += $seconds;
                $week_dict['totalworktimes'] += $seconds;
            };

            
            
            // $this->logger->debug(sprintf(
            //     "week_dict after: %s",
            //     $week_dict[$current_day_name]
            // ));

            // Last entry of the timesheet
            if ($index == $lastDateIndex) {
                // $week_dict['weeknumber'] = $last_week;
                $week_dict['weeknumber'] = $current_week;
                array_push($week_list, $week_dict);
            }
            // } else {
            //     $week_dict[$current_day_name] = $seconds;
            //     $this->logger->debug(sprintf(
            //         "week_dict new: %s",
            //         $week_dict[$current_day_name]
            //     ));
            // }
            // $week_dict['totalworktimes'] += $seconds;

            // $this->logger->debug('{index}', ['index' => $index]);
            // $this->logger->debug('{lastDateIndex}', ['lastDateIndex' => $lastDateIndex]);
            // $this->logger->debug('{week_end_date}', ['week_end_date' => $week_end_date]);
            // $this->logger->debug('{current_date}', ['current_date' => $current_date]);
            
            
            $seconds = 0;            
        };


        return $this->render('@WeeklyOverview/index.html.twig', [
            'user' => $user,
            'pageTitle' => $pageTitle,
            // 'period' => new DailyStatistic($week_start_date, $week_end_date, $user),
            'timesheets_weekly' => $timesheet,
            'week_list' => $week_list,
        ]);
    }
}
