<!DOCTYPE html>
<head>
      <title>Student assessment reporting system</title>  
		<style> .error {color: #FF0000;}    </style>
</head>
<body>      
<?php   

$studentIDErr = "";   $reportTypeErr = "";     
$studentID = "";    $reportType = "";   $headingText = "";

$studentsJSON = file_get_contents("./data/students.json");
$studentsData = json_decode($studentsJSON, true);
$assessmentsJSON = file_get_contents("./data/student-responses.json");
$assessmentsData = json_decode($assessmentsJSON, true);
$questionsJSON = file_get_contents("./data/questions.json");
$questionsData = json_decode($questionsJSON, true);

function test_input($data) 
	{        
		$data = trim($data);        
		$data = stripslashes($data);        
		$data = htmlspecialchars($data);        
		return $data;       
	 }  
	 function getHeadingText($reportType){
	 	$reportStr = "";
	 	switch ($reportType) 
			{
			    case "1":
			        $reportStr = "Diagnostic";
			        break;
			    case "2":
			        $reportStr = "Progress";
			        break;
			    case "3":
			        $reportStr = "Feedback";
			        break;
			    default:
			}
			return $reportStr;
	 }  
	 function getStudentFullName($studentID, $studentsData){
	 	foreach ($studentsData as $student_data) {
	 				if($student_data["id"] === $studentID){
  					$selectedStudentName = $student_data["firstName"]. " ".$student_data["lastName"];
  					}
				}
		 return $selectedStudentName;
	 }

	 function getfilteredStudentResponse($studentID, $assessmentsData){
	 	$selectedStudent = array();
            foreach($assessmentsData as $studentResponse) {
            	
                if ($studentID === $studentResponse["student"]["id"] && isset($studentResponse["completed"])) 
                {
                    array_push($selectedStudent,$studentResponse);
                }	
            }
        return $selectedStudent;    
	 }
	 function getselectedResponseRecord($filteredStudentResponse){
	 
		usort($filteredStudentResponse, function ($a, $b) {
		    $dateA = DateTime::createFromFormat('d/m/Y H:i:s', $a['completed']);
		    $dateB = DateTime::createFromFormat('d/m/Y H:i:s', $b['completed']);
	    	// use >= for ascending ordering, use `<=` for descending
	    	return $dateA <= $dateB;
		});
	 	return $filteredStudentResponse[0];
	 }

	 function getStrandScores($selectedResponseRecord, $questionsData){
	 	$scoresArray = array();
	 	foreach($selectedResponseRecord["responses"] as $responses){
	 		
 			foreach($questionsData as $questions) {
 				if($responses["questionId"] === $questions["id"])
 				{
 					if($responses["response"] === $questions["config"]["key"]) {
 						array_push($scoresArray, $questions["strand"]);
 					}
 				}
 			}
	 	}
	 	return array_count_values($scoresArray);
	 }

	 function getStrandTotals($questionsData){
	 	$totals = array();
	 	foreach($questionsData as $questions) {
	 		array_push($totals, $questions["strand"]);
	 	}
	 	return array_count_values($totals);
	 }

	 function getWrongQADetails($selectedResponseRecord, $questionsData){
	 	$wrongQuestionArray = array();
	 	
	 	foreach($selectedResponseRecord["responses"] as $responses){
	 		foreach($questionsData as $questions) {
	 			if($responses["questionId"] === $questions["id"])
 				{
		 			if($responses["response"] !== $questions["config"]["key"]) {
		 				array_push($wrongQuestionArray, $questions);

	 				}
 				}
	 		}
	 	}
	 	return $wrongQuestionArray;
	 }

	 function getWrongAnswerData($selectedResponseRecord, $questionsData){
	 	$submittedAnsArr = array();

	 	foreach($selectedResponseRecord["responses"] as $responses){
	 		foreach($questionsData as $questions) {
	 			if($responses["questionId"] === $questions["id"])
 				{
		 			if($responses["response"] !== $questions["config"]["key"]) {
		 				array_push($submittedAnsArr, $responses["response"]);

	 				}
 				}
	 		}
	 	}
	 	return $submittedAnsArr;
	 }
	 function checkStudentIds($studentID, $studentsData) {
	 	$isExisting = false;
	 	$stdIdArray = array();
	 	foreach($studentsData as $stdata){
	 		array_push($stdIdArray, $stdata["id"]);
	 	}
	 	$isExisting = in_array($studentID, $stdIdArray);
	 	
	 	return $isExisting;
	 }

if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{        
		if (empty($_POST["studentID"])) 
		{            
			$studentIDErr = "Please enter valid student id";        
		} 
		else 
		{            
			$studentID = test_input($_POST["studentID"]); 

			// check if studentID is valid          
			if (!preg_match("/student/i",$studentID)) 
			{            
				$studentIDErr = "Please enter valid student id (Hint: Enter 'student' followed by a number)";            
			}        
		}   

		if (empty($_POST["reportType"])) 
		{            
			$reportTypeErr = "Please enter 1, 2 or 3";        
		} 
		else 
		{            
			$reportType = test_input($_POST["reportType"]);            
			// check if reportType is a number         
			if (!preg_match("/^[1-3][0-9]*$/",$reportType)) 
			{            
				$reportTypeErr = "Enter 1, 2 or 3";            
			}        
		}          
	}  else{
		//Log Message
	} 
   
    
	?>  
	
		<h2>Student assessment reporting system</h2>
      
      	<p>Please enter the following</p> 
 
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
		        
				<p>Student ID: <input type="text" name="studentID" maxLength ="8" placeholder="Enter student ID" value="<?php if (isset($_POST['studentID'])) echo $_POST['studentID']; ?>"/>
				<span class="error">* <?php echo $studentIDErr;?></span> 
				</p>
      			<p>Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback):
      			<input type="text" name="reportType" maxLength ="1" placeholder="Enter 1, 2 or 3" value="<?php if (isset($_POST['reportType'])) echo $_POST['reportType']; ?>"/>
      			<span class="error">* <?php echo $reportTypeErr;?></span> 
      			</p>
      			<input type="submit" name="submit" value="Submit">   
		      
		</form>   

		<?php if (!empty($_POST) && $studentID != "" && checkStudentIds($studentID, $studentsData) &&
		$reportType != "" && (int)$reportType != 0 && (int)$reportType <= 3 ){ ?>
		<div>
      		<?php 
      		$headingText = getHeadingText($reportType);
      		echo "<h2>".$headingText." report:</h2>";   
			
			$selectedStudentName = getStudentFullName($studentID, $studentsData);
            $filteredStudentResponse = getfilteredStudentResponse($studentID, $assessmentsData);
            $selectedResponseRecord = getselectedResponseRecord($filteredStudentResponse);
            $dateTimeCompleted = DateTime::createFromFormat('d/m/Y H:i:s', $selectedResponseRecord['completed']);
			$dateTimeCompletedFormatted = $dateTimeCompleted->format('jS F, Y, H:i A');
		    $rawScore = $selectedResponseRecord["results"]["rawScore"];
            $totalScore = count($selectedResponseRecord["responses"]);

		if($reportType === "1"){
			echo "<p>".$selectedStudentName. " recently completed Numeracy assessment on ".$dateTimeCompletedFormatted."</p>";
            echo "<p>He got ".$rawScore. " questions right out of ".$totalScore.". Details by strand given below:</p><p></p>";
		    $strandScores = getStrandScores($selectedResponseRecord, $questionsData);
		    $strandTotals = getStrandTotals($questionsData);
		    
		    echo "<p> Numeracy and Algebra: ".$strandScores["Number and Algebra"]. " out of ".$strandTotals["Number and Algebra"]." correct<br/>";
		    echo "Measurement and Geometry: ".$strandScores["Measurement and Geometry"]. " out of ".$strandTotals["Measurement and Geometry"]." correct<br/>";
		    echo "Statistics and Probability: ".$strandScores["Statistics and Probability"]. " out of ".$strandTotals["Statistics and Probability"]." correct</p>";
		}elseif($reportType === "2"){
			echo "<p>".$selectedStudentName. " has completed Numeracy assessment ".count($filteredStudentResponse)." times in total. Date and raw score given below:</p><p></p><p>";
			
			foreach($filteredStudentResponse as $response){
				$dateAssigned = DateTime::createFromFormat('d/m/Y H:i:s', $response['assigned']);
				$dateAssignedFormatted = $dateAssigned->format('jS F, Y');
				echo $dateAssignedFormatted.", Raw Score: ".$response["results"]["rawScore"]." out of ".$totalScore = count($response["responses"])."<br>";
			}
			echo "</p>";
		}
		elseif($reportType === "3"){
			echo "<p>".$selectedStudentName. " recently completed Numeracy assessment on ".$dateTimeCompletedFormatted."</p>";
			echo "<p>He got ".$rawScore. " questions right out of ".$totalScore.". </p>";
			$wrongQADetails = getWrongQADetails($selectedResponseRecord, $questionsData);
			$wrongAnswerData = getWrongAnswerData($selectedResponseRecord, $questionsData);
			if((int)$rawScore < (int)$totalScore) {
				echo "<p>Feedback for wrong answers given below</p>";
				foreach($wrongQADetails as $details) {
					echo "<p>";
					echo $details["stem"]."<br/>";
					foreach($wrongAnswerData as $wrongAnswerOption){
					
						foreach($details["config"]["options"] as $options){

							if($details["config"]["key"] == $options["id"]) {
								$rightAnsLabel = $options["label"];
								$rightAnsValue = $options["value"];
							}
							
						}
						foreach($details["config"]["options"] as $options){

							if($wrongAnswerOption == $options["id"]) {
								$wrongAnsLabel = $options["label"];
								$wrongAnsValue = $options["value"];
							}
							
						}
					}
					echo "Your answer: ".$wrongAnsLabel." with value ".$wrongAnsValue."<br/>";
				    echo "Right answer: ". $rightAnsLabel ." with value ".$rightAnsValue ."<br/>";
					echo $details["config"]["hint"];
					echo "</p>";
				}
			}
			
		}

		?>
      	</div>
		<?php } ?>
		</body>
	</html>