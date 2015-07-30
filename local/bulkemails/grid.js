$(document).ready(function() {

	

        $('#example').dataTable( {

        "ajax": "data.php",

        "columns": [

            { "data": "id" },
            { "data": "email" },
			{ "data": "from" },
            { "data": "to" },
            { "data": "subject" },
            { "data": "message" },
			{ "data": "status" },
            { "data": "timestart" },
			{ "data": "timeend" },
			{"data":"next_mail"},
			{"data":"email_responce"}

			//{ "data": "created" }

            

        ]

    } );

        

        

	/*$('#example').dataTable( {

		"ajax": 'http://localhost/lms27/cmenu/arrays.txt'

	} );*/

} );