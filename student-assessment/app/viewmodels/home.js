    define(['durandal/app', 'knockout'], function (app, ko) {
    
    var ctor = function () {
        var self = this;

        self.studentID = ko.observable("").extend({ required: "" });
        self.reportType = ko.observable("").extend({ required: "" });

        //var errorMessge = "<span class='red'>Please Enter Student ID and Report to generate</span>";
        var studentsData = [
            {
                "id": "student1",
                "firstName": "Tony",
                "lastName": "Stark",
                "yearLevel": 6
            },
            {
                "id": "student2",
                "firstName": "Steve",
                "lastName": "Rogers",
                "yearLevel": 6
            },

            {
                "id": "student3",
                "firstName": "Peter",
                "lastName": "Parker",
                "yearLevel": 6
            }

        ];

        self.students = studentsData;

 
        self.getReport = ko.computed(function () {
          var retVal ="";
            if (self.studentID() !== '' && self.reportType() !== '') {
                retVal = self.reportType();
            }
            return retVal;
        });

        self.reportHeading = ko.computed(function () {
            var headingText = "";
            var reportVal = self.getReport();

            switch (reportVal) {
                case "1":
                    headingText = "Diagnostic";
                    break;
                case "2":
                    headingText = "Progress";
                    break;
                case "3":
                    headingText = "Feedback";
                    break;
                default:
            }
            return headingText;
        });

        self.isDiagnostic = ko.computed(function () {
            var isDiagnostic = false; 
            if(self.getReport() === "1"){
              isDiagnostic = true;
            } 
            return isDiagnostic;
        });
        self.isProgress = ko.computed(function () {
            var isProgress = false; 
            if(self.getReport() === "2"){
              isProgress =true;
            }
            return isProgress;
        });
        self.isFeedback = ko.computed(function () {
            var isFeedback = false;
            if(self.getReport() === "3"){
              isFeedback = true;
            }
            return isFeedback;
        });

        self.selectedStudentName = ko.computed(function () {
            var fullName = "";
            self.students.forEach(function (student) {
                if (self.studentID !== '' && self.studentID === student.id) {
                    fullName = student.firstName + " " + student.lastName;
                }
            });
            return fullName;
        });

        /*self.filteredStudentResponse = ko.computed(function () {
            var selectedStudent = [];
            if (self.studentID() !== "") {
                self.studentResponses().forEach(function (studentResponse) {
                    if (self.studentID() === studentResponse.student.id()) {
                        selectedStudent.push(studentResponse);
                    }
                });

                var mySortedArray = selectedStudent.sort(
                    (left, right) => (parseInt(left.id().slice(-1)) < parseInt(right.id().slice(-1))) ? 1 :
                        (parseInt(left.id().slice(-1)) > parseInt(right.id().slice(-1))) ? -1 : 0);
                return mySortedArray[0];
            }
        });*/

    }
        
    return ctor;
         
    });