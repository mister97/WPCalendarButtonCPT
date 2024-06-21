<?php // Shortcode for Add to Calendar button
function addtoical_function() {
     return '<form action="'.get_feed_link("calendar-event").'" method="post">
    			<input hidden="hidden" name="eventID" value="'.get_the_id().'">
  				<button title="Add to iCal" type="submit" name="iCalForm">Add To iCal</button>
			</form>';
}
add_shortcode('addtoicalbutton', 'addtoical_function');

//Event ICAL feed
class SH_Event_ICAL_Export  {

    public function load() { add_feed('calendar-event', array(__CLASS__,'export_events')); }

    // Creates an ICAL file of events in the database
    public function export_events(){ 

        //Give the iCal export a filename
        $filename = urlencode( 'event-ical-' . date('Y-m-d') . '.ics' );

        //Collect output 
        ob_start();

        // File header
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Content-type: text/calendar");
        header("Pragma: 0");
        header("Expires: 0");
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//<?php echo get_bloginfo('name'); ?> //NONSGML Events //EN
CALSCALE:GREGORIAN
<?php // Query for events

    if(isset($_POST['iCalForm'])) {
        $post_ID = $_POST['eventID'];
        $events = new WP_Query(array(
            'p' => $post_ID,
            'post_type' => 'events'  //Or whatever the name of your post type is
        ));
		
	// iCal date format: yyyymmddThhiissZ
	// PHP equiv format: Ymd\This

	function dateToCal($time) {
		return date('Ymd\This', $time) . 'Z';
	}

    if($events->have_posts()) : while($events->have_posts()) : $events->the_post();
        $uid = get_the_ID(); // Universal unique ID
        $dtstamp = date_i18n('Ymd\THis\Z',time(), true); // Date stamp for now.
        $created_date = get_post_time('Ymd\THis\Z', true, get_the_ID() ); // Time event created
		$location = get_post_custom_values('location',get_the_ID()); // Location of the event taked from custom post type
		$datefrom = get_post_custom_values('from',get_the_ID()); // Start date & time
		$dateto = get_post_custom_values('to',get_the_ID()); // End date & time
		$url = get_permalink(); //Event URL
		$summary = get_the_title(); // Event Title
        // Other pieces of "get_post_custom_values()" that make up for the StartDate, EndDate, EventOrganiser, Location, etc.
?>
BEGIN:VEVENT
DTEND:<?php echo dateToCal($dateto[0]); ?>

UID:<?php echo $uid;?>

DTSTAMP:<?php echo $dtstamp;?>

LOCATION:<?php echo $location[0];?>

DESCRIPTION: Event organised by...

URL;VALUE=URI:<?php echo $url; ?>

SUMMARY:<?php echo $summary; ?>

DTSTART:<?php echo dateToCal($datefrom[0]); ?>

END:VEVENT

<?php endwhile; endif; } ?>
END:VCALENDAR
<?php //Collect output and echo 
    $eventsical = ob_get_contents();
    ob_end_clean();
    echo $eventsical;
    exit();
    }   

} // end class
SH_Event_ICAL_Export::load();
