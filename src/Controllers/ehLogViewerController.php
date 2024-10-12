<?php

namespace ScottNason\EcoHelpers\Controllers;

use Illuminate\Http\Request;
use ScottNason\EcoHelpers\Classes\ehLayout;
use ScottNason\EcoHelpers\Classes\ehLinkbar;


//class ehLogViewerController extends Controller
class ehLogViewerController extends ehBaseController
{
    /**
     * Pull the version.php file for display and show a total time spent.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function devLog(Request $request) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check the state of the radio button and set the default (first time through) value for the which log filter.
        // Note that base_path() for UnderCover site:  /home/dh_s4mvcz/_sites_private/_private_undercover
        $dev_log_file = base_path().'/config/version.php';
        if (empty($request->filter_which_log)) {
            $request->filter_which_log = "1";
            // Pull the base Laravel applications version.php file
            $dev_log_file = base_path().'/config/version.php';
        } else {
            if ($request->filter_which_log == "2") {
                // Pull the ecoFramework version.php file
                $dev_log_file = base_path().'/vendor/scott-nason/eco-helpers/src/version.php';
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Page setup
        ehLayout::initLayout();
        ehLayout::setOptionBlock(false);
        ehLayout::setName("Development Log History");
        ehLayout::setFullWidth(false);

        $linkbar = new ehLinkbar();
        ehLayout::setLinkbar($linkbar->getLinkbar());

        if ($request->filter_which_log == "1") {
            ehLayout::setDynamic('config/version.php');
            ehLayout::setAttention('Viewing the development history for this application');
        } else {
            // For now assuming that if it's not 1 it must be 2.
            ehLayout::setDynamic('eco-helpers/version.php');
            ehLayout::setAttention('Viewing the development history for the ecoHelpers framework');
        }


        $form['layout'] = ehLayout::getLayout();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Set the form action
        $form['layout']['form_action'] = route('dev-log');
        $form['layout']['form_method'] = 'GET';           // Set the update() form method.



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Get the version.php file for the radio button selection above.
        $form['dev_log'] = "no version file found";

        if (file_exists($dev_log_file)) {
            // nl2br converts the unix line feeds into <br/>
            //$form['dev_log'] = nl2br(file_get_contents(base_path().'/config/version.php'));

            $form['dev_log'] = file_get_contents($dev_log_file);
        }

        // Turn the whole text file into a line by line array.
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $form['dev_log']) as $line){

            // Just a little cleanup. Remove the lines that we don't want to display.
            if(str_contains($line,"<?php")){
                $line = '';
                continue;
            }
            if(str_contains($line,"return [")){
                $line = '';
                continue;
            }
            if(str_contains($line,"];")){
                $line = '';
                continue;
            }

            // Replace any tag-like identifiers
            $line = str_replace("<", "&lt;", $line);
            $line = str_replace(">", "&gt;", $line);

            // Save whatever is left into the array for the template to use.
            $form['dev_log_as_array'][] = $line."<br/>";
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Turn each line into a data array:
        // 0-date
        // 1-version
        // 2-time
        // 3-did what
        foreach($form['dev_log_as_array'] as $line){
            $form['dev_log_as_data'][] = explode(":",$line);
        }

        // Then, add up the total time spent (for any line that has a [2] key).
        $form['total_time_spent'] = 0;
        foreach($form['dev_log_as_data'] as $line){
            if(!empty($line[2])) {
                $form['total_time_spent'] = $form['total_time_spent'] + $line[2];
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        return view('ecoHelpers::core.eh-dev-log-display',[
            'form' => $form,
        ]);

    }
}
