<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

use DB;

class Report extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:report
                            {--type= : Report type}
                            {--query= : Free form report query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run reports against database';

    /**
     * The report type.
     *
     * @var string
     */
    protected $type;

    /**
     * The report query.
     *
     * @var string
     */
    protected $query;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();

        if(isset($options['type'])):
            $this->type = $options['type'];
            $this->runReport();
        endif;

        if(isset($options['query'])):
            $this->query = $options['query'];
            if (!is_valid_read_query($this->query)):
                $this->error('Only read queries are authorized');
                exit;
            endif;
            $this->runDynamicReport();
        endif;

        // Validate parameters.
        if(empty($this->type) && empty($this->query)):
            $this->error('A report type or report query is required');
        endif;

    }

    /**
     * Run report.
     *
     */
    protected function runReport()
    {
        try {

            switch($this->type):
                case 'countries':
                    $query = 'SELECT country, COUNT(*) AS total FROM artists GROUP BY country ORDER BY total DESC';
                    $records = DB::select($query);
                    $this->createCSVReport('artist_country.csv', $records, ['Country', 'Total']);
                    break;
                case 'cities':
                    $query = "SELECT artist, country, notes FROM artists WHERE notes like '%Location%' ORDER BY country ASC, notes ASC, artist ASC";
                    $records = DB::select($query);
                    $this->createCSVReport('artist_city.csv', $records, ['Artist', 'Country', 'Location']);
                    break;
                default:
                    $this->error('This report type does not exist.');
            endswitch;

        } catch (Exception $e) {
            $this->error("$e");
        }
    }

    /**
     * Run dynamic report.
     */
    protected function runDynamicReport()
    {
        try {

            $records = DB::select($this->query);
            $this->createCSVReport('report-' . date('YmdHi') . '.csv', $records);

        } catch (Exception $e) {
            $this->error("$e");
        }
    }

    /**
     * Create CSV file
     *
     * @param String $filename Name for CSV file
     * @param Array $records Report data $paramname
     * @param Array $header Report header
     */
    protected function createCSVReport(String $filename, Array $records, Array $header = null) {
        if (isset($records[0])):

            $handle = fopen($filename, 'w');

            if (!$header):
                 $header = (array) $records[0];
                 $header = array_keys($header);
            endif;

            fputcsv($handle, $header);

            foreach($records as $record):
                $record = (array) $record;
                fputcsv($handle, array_values($record));
            endforeach;

            fclose($handle);
            $this->info('The report has been run successfully.');

        else:
            $this->error('No records were returned by this query');
        endif;

    }

}