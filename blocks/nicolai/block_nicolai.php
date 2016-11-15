<?php

//include 'inc/dbconn.php';
//include 'inc/dblayer.php';

//Start of the "block_nicolai class"
//It extends the base in the block object
class block_nicolai extends block_base {
    
    //An initialize function that get's the title nicolai for the block
    public function init() {
        $this->title = get_string('nicolai', 'block_nicolai');
    }

    //A function that gets content
    public function get_content()
    {

        //This makes sure there's no performance hit when the
        //content has already been created
        if ($this->content !== null) {
            return $this->content;
        }

        //Here I create a generic class
        $this->content = new stdClass;

        $this->content->text = "";
        $form = new usersAttended_form();

        if ($form->is_cancelled()) {
            //do something
        //} else if ($fromform = $form->get_data()) { //doesn't do anything yet
            //do something else
        } else {
            //$form->set_data($toform); //doesn't do anything yet
            $this->content->text = $form->render();
        }

        return $this->content;

        /*
        $dbhost = "127.0.0.1";
        $dbuser = "nikolaidev";
        $password = "Passord123.";
        $dbname = "nikolaidev";
        
        $conn = new mysqli($dbhost, $dbuser, $password, $dbname);
        $link = mysqli_connect($dbhost, $dbuser, $password, $dbname);
        
        if ($conn->connect_error) {
            echo("Connection failed:" . $conn->connect_error);
        }
        
        $query = "SELECT person.personID, person.fornavn, person.etternavn, kursinstans.kursinstans, paamelding.kursinstansID 
                  FROM person 
                  JOIN paamelding ON person.PersonID = paamelding.PersonID 
                  JOIN kursinstans ON paamelding.KursinstansID = kursinstans.KursinstansID";
        
        if (mysqli_multi_query($link, $query)){
            do {
                // store first result set
                if ($result = mysqli_store_result($link)) {
                    while ($row = mysqli_fetch_row($result)) {
                        printf("%s\n", $row[0]);
                    }
                    mysqli_free_result($result);
                }
                // print divider
                If (mysqli_more_result($link)) {
                printf("----------\n");
                }
            } while (mysqli_next_result($link));
        }

        */

    }
}
