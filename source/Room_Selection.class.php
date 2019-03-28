<?php 
Class Room_Selection
{
	private $roomNumber;
	private $resdate;
	private $startTime;
	private $finishTime;
	private $capacity;
	private $availableRoomQuery;
	
	public function SelectCapacity($c)
	{
		$this->capacity = $c;
		//$_COOKIE['capacity'] = $this->capacity;
	}
	
	public function SelectRoom($r)
	{
		$this->roomNumber = $r;
		$expiryTime = time()+60*60*24;
        setcookie("roomNumber", $this->roomNumber, $expiryTime);
	}	
	public function SelectTimeWithForm($d, $s,$f)
	{
		$this->startTime = $d." ".$s;
		$this->finishTime = $d." ".$f;
		$expiryTime = time()+60*60*24;
		setcookie("startTime", $this->startTime, $expiryTime);
		setcookie("finishTime", $this->finishTime, $expiryTime);
	}
	public function SelectTimeWithCookie($s,$f)
	{
		$this->startTime = $s;
		$this->finishTime = $f;
	}
	public function FindAvailableRooms()
	{
		try {
		   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
		   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		   $sql = "SELECT Room_ID, Building from studyroom 
					where ".$this->capacity." <= Capacity 
					AND Room_ID not in
					(select Room_ID  from roomschedule where startTime between '".$this->startTime."' AND '".$this->finishTime." 
					' AND endTime between '".$this->finishTime."' AND '".$this->startTime."')";
		   $result = $pdo->query($sql);
		   $pdo = null;
		}
		catch (PDOException $e) {
		   die( $e->getMessage() );
		}
		$this->availableRoomQuery = $result;
	}
	public function printAvailableRooms()
	{
		$returnstring = "<h3>Click a room to reserve:</h3>";
		while ($row = $this->availableRoomQuery->fetch()) 
		{
			$returnstring = $returnstring."<a data-toggle='modal' href='#".$row['Room_ID']."Modal'>".$row['Building']." ".$row['Room_ID']."</a>\n";
			$returnstring = $returnstring."	<div id='".$row['Room_ID']."Modal' class='modal fade' role='dialog'>
											  <div class='modal-dialog'>

											    <!-- Modal content-->
											    <div class='modal-content'>
											      <div class='modal-header'>
											        <button type='button' class='close' data-dismiss='modal'>&times;</button>
											        <h4 class='modal-title'>Book this room</h4>
											      </div>
											      <div class='modal-body'>
											        <p>Details: Room: ".$row['Room_ID']." in the ".$row['Building']." building from ".$this->startTime." to ".$this->finishTime."</p>
											      </div>
											      <div class='modal-footer'>
											      	<form action='enterReservation.php' method='get'>
											        	<button type='submit' class='btn btn-default' name='roomNumber' value='".$row['Room_ID']."'>Submit</button>
											        </form>
											        <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
											      </div>
											    </div>

											  </div>
											</div>
										";
		}


		return $returnstring;
	}
	public function ConfirmRoom()
	{
		try {
		   $pdo = new PDO(DBCONNSTRING,DBUSER,DBPASS);
		   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		   $sql = "INSERT INTO reservation (StudentID)
					VALUES (".$_SESSION['studentID'].");";
		 if($pdo->exec($sql) === false){
			 echo 'Error';
			 }else{
			 echo "Reservation has been entered";
		 }
		 $sql = "INSERT INTO roomschedule (Reserv_ID, Room_ID, startTime, endTime )
					values (LAST_INSERT_ID(),".$this->roomNumber.",'".$this->startTime."', '".$this->finishTime."');";
		 if($pdo->exec($sql) === false){
			 echo 'Error';
			 }else{
			 echo "Reservation has been entered";
		 }

		}
		catch (PDOException $e) {
		   die( $e->getMessage() );
		}

	}
}
?>