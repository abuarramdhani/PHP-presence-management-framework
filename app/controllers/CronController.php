<?php

class CronController extends Controller{

    private $_activity_model;
    private $_user_model;
    
    public function __construct($dependencies, $action, $params) {
  
        global $CONFIG;
            
        // get the dependencies
        $this->_dependencies = $dependencies;
     
        // instantiate the models
        $this->_activity_model = new ActivityModel($this->_dependencies);
        $this->_user_model = new UserModel($this->_dependencies);
        $this->_interval_model = new IntervalModel($this->_dependencies);

        // check if the required action is defined
        if (method_exists($this, $action)) {
            $this->$action($params);
        }
    }

    private function run() {
    
        global $CONFIG;

        $verbose = $CONFIG['verbose'];

        //get all users
            $time_start = microtime(true);
            $verbose && print_r("Fetching All Users");
        
        $users = $this->_user_model->get_all_users();
            
            $time_end = microtime(true);
            ($verbose && $users) ? print_r("...Ok ".($time_end-$time_start)." ms\n") : print_r("...Failed!\n");
        
        foreach($users as $user) {
            
            //get activity without incidences
            //TODO: get only not calculated activity
                $time_start = microtime(true);
                $verbose && print_r("Fetching activity of user with id ".$user['id']);

            $activity = $this->_activity_model->get_user_activity_no_incidence($user['id']);
                
                $time_end = microtime(true);
                ($verbose && $activity) ? print_r("...Ok ".($time_end-$time_start)." ms\n") : print_r("...No Activity!\n");

            if($activity){
                //group 2 by 2
                $grouped_activities = null;
                for($i=0; $i<count($activity); $i=$i+2){
                    //group only when we have both members
                    $activity[$i] && $activity[$i+1] && $grouped_activities[] = array($activity[$i], $activity[$i+1]);
                }

                //compute the intervals 
                $time_start = microtime(true);
                $verbose && print_r("Computing intervals");

                $intervals = null;
                foreach($grouped_activities as $gactivity){
                    
                    //check data integrity
                    if($gactivity[0]['action'] == 'checkin' && $gactivity[1]['action'] == 'checkout'){
                      
                        //compute the interval
                        $date_start = new DateTime(date(DATE_ATOM, $gactivity[0]['timestamp']));
                        $date_end = new DateTime(date(DATE_ATOM, $gactivity[1]['timestamp']));
                        $date_diff = $date_start->diff($date_end);

                        $interval = new stdClass();
                        $interval->userid = $gactivity[0]['userid'];
                        $interval->timestart = $gactivity[0]['timestamp'];
                        $interval->timestop = $gactivity[1]['timestamp'];
                        $interval->y = $date_diff->y;
                        $interval->m = $date_diff->m;
                        $interval->d = $date_diff->d;
                        $interval->h = $date_diff->h;
                        $interval->i = $date_diff->i;
                        $interval->s = $date_diff->s;

                        $intervals[] = $interval;
                    
                    }else{
                        die(print_r("FATAL: Corrupted database!"));
                    }
                }

                    $time_end = microtime(true);
                    $verbose && print_r("...Done ".($time_end-$time_start)." ms\n");

                //save the intervals to the DB
                if($intervals){
                    
                        $time_start = microtime(true);
                        $verbose && print_r("Storing intervals into the database");

                            $this->_interval_model->store($intervals);

                        $time_end = microtime(true);
                        $verbose && print_r("...Done ".($time_end-$time_start)." ms\n");
                
                }
            }

        }
    }

}
