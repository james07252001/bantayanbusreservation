<?php
include('includes/layout-header.php');

$schedule_id = isset($_GET['schedule_id']) && !empty($_GET['schedule_id']) ? $_GET['schedule_id'] : "";
if(empty($schedule_id)){
    header('Location: index.php');
} else {
    include('controllers/db.php');
    include('controllers/schedule.php');

    $database = new Database();
    $db = $database->getConnection();

    $new_schedule = new Schedule($db);
    $schedule = $new_schedule->getById($schedule_id);
    $fare = $schedule['fare'];

    if(empty($schedule["id"])){
        header('Location: index.php');
    } else {
        include('controllers/route.php');
        include('controllers/location.php');
        include('controllers/book.php');
        include('controllers/bus.php');
        include('controllers/vessel.php');
        include('controllers/driver.php');
        include('controllers/conductor.php');
        
        $new_route = new Route($db);
        $route = $new_route->getById($schedule["route_id"]);

        $new_bus = new Bus($db);
        $bus = $new_bus->getById($schedule["bus_id"]);

        $new_driver = new Driver($db);
        $driver = $new_driver->getById($schedule["driver_id"]);

        $new_conductor = new Conductor($db); 
        $conductor = $new_conductor->getById($schedule["conductor_id"]);

        $new_location = new Location($db);
        $location_from = $new_location->getById($route["route_from"]);
        $location_to = $new_location->getById($route["route_to"]);
    }
}
?>

<main class="mt-5">
    <div class="container">
        <div class="bg-white shadow-sm w-100 m-auto" style="max-width: 500px">
            <div class="p-3 bg-primary">
                <h4 class="mb-0 text-center">
                    <?php echo $location_from["location_name"].' &#x2192; '.$location_to["location_name"] ?>
                </h4>
            </div>
            <div class="p-3">
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Date:</span>
                    <strong><?php echo date_format(date_create($schedule['schedule_date']),'F j, Y') ?></strong>
                </p>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Status:</span>
                    <strong class="text-uppercase"><?php echo $schedule['status'] ?></strong>
                </p>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Departure Time:</span>
                    <strong><?php echo date_format(date_create($schedule['departure']), 'g:i A') ?></strong>
                </p>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Arrival Time:</span>
                    <strong><?php echo date_format(date_create($schedule['arrival']), 'g:i A') ?></strong>
                </p>
                <hr>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Bus Driver:</span>
                    <strong><?php echo $driver['name'] ?></strong>
                </p>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Bus Conductor:</span>
                    <strong><?php echo $conductor['name'] ?></strong>
                </p>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Bus Name:</span>
                    <strong><?php echo $bus['bus_num'] ?></strong>
                </p>

                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Bus Number:</span>
                    <strong><?php echo $bus['bus_code'] ?></strong>
                </p>

                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Bus Type:</span>
                    <strong><?php echo $bus['bus_type'] ?></strong>
                </p>
                <p class="d-flex align-items-center justify-content-between mb-0">
                    <span class="text-muted d-block">Fare:</span>
                    <strong id="fare"><?php echo $schedule['fare'] ?></strong>
                </p>
                
                <hr>
                
                <!-- Passenger Type selection and file upload -->
                <div class="form-group">
                    <label for="passengerType">Passenger Type:</label>
                    <select id="passengerType" class="form-control">
                        <option value="regular">Regular</option>
                        <option value="student">Student</option>
                        <option value="senior">Senior Citizen</option>
                        <option value="pwd">Person with Disability (PWD)</option>
                    </select>
                </div>
                <div class="form-group" id="uploadIdSection" style="display: none;">
                    <label for="uploadId">Upload ID:</label>
                    <input type="file" id="uploadId" class="form-control">
                </div>
                
                <hr />

                <!-- Seat Selection -->
                <div>
                    <div class="my-3">
                        <p class="mb-0">Legend</p>
                        <div class="d-flex">
                            <div class="d-flex align-items-center mr-2">
                                <span style="height: 16px; width: 16px; background-color: #007bff; opacity: .65;" class="d-inline-block mr-2"></span>
                                <span>Reserved</span>
                            </div>
                            <div class="d-flex align-items-center mr-2">
                                <span style="height: 16px; width: 16px;" class="bg-dark d-inline-block mr-2"></span>
                                <span>Selected</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span style="height: 16px; width: 16px; border: 2px solid black" class="d-inline-block mr-2"></span>
                                <span>Available</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark text-white text-center">Front</div>
                    <div class="py-0">
                        <table class="table table-borderless">
                            <tbody>
                                <?php
                                    $seat_row_num = 0;
                                    for ($i = 1; $i <= 10; $i++) {
                                        echo '<tr>';
                                        for ($x = 1; $x <= 5; $x++) {
                                            if($x == 3){
                                                echo '<td>&nbsp;</td>';
                                            } else {
                                                $seat_row_num++;
                                                $new_book = new Book($db);
                                                $book = $new_book->checkSeat($schedule["id"], $seat_row_num);

                                                if(empty($book["id"])){
                                                    echo '<td><button data-seat="'.$seat_row_num.'" class="btn-seat btn btn-sm btn-block btn-outline-dark" style="background-image: url(\'assets/img/seats.png\'); background-size: cover; height: 50px;width: 50px;">'.$seat_row_num.'</button></td>';
                                                } else {
                                                    echo '<td><button class="btn btn-sm btn-block btn-primary" disabled style="background-image: url(\'assets/img/seats.png\'); background-size: cover; height: 50px;width: 50px;">'.$seat_row_num.'</button></td>';
                                                }
                                            }
                                        }
                                        echo '</tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-dark text-white text-center">Back</div>
                </div>
                <hr />
                
                <!-- Fare, Discount, and Total -->
                <div id="fareSection">
                    <p class="d-flex align-items-center justify-content-between mb-0">
                        <span class="text-muted d-block">Fare per Seat:</span>
                        <strong id="farePerSeat"><?php echo $schedule['fare'] ?></strong>
                    </p>
                    <p class="d-flex align-items-center justify-content-between mb-0">
                        <span class="text-muted d-block">Discount Amount:</span>
                        <strong id="discountAmount">0.00</strong>
                    </p>
                    <p class="d-flex align-items-center justify-content-between mb-0">
                        <span class="text-muted d-block">Total:</span>
                        <strong id="total">0.00</strong>
                    </p>
                </div>

                <hr />
                <div class="text-right">
                    <a href="index.php" class="btn btn-outline-dark">Cancel</a>
                    <button id="booked" class="btn btn-dark">Book</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include('includes/scripts.php')?>

<script>
    const from = '<?php echo $location_from["location_name"] ?>';
    const to = '<?php echo $location_to["location_name"] ?>';
    const routeName = `${from.slice(0, 3)}-${to.slice(0, 3)}`;

    const isVerified = '<?php echo isset($_SESSION["isVerified"]) && !empty($_SESSION["isVerified"]) ? $_SESSION["isVerified"] : false ?>';
    const schedule_id = '<?php echo isset($_GET['schedule_id']) && !empty($_GET['schedule_id']) ? $_GET['schedule_id'] : false ;?>';
    const passenger_id = '<?php echo isset($_SESSION["userId"]) && !empty($_SESSION["userId"]) ? $_SESSION["userId"] : false ;?>';
    const passenger_email = '<?php echo isset($_SESSION["userEmail"]) && !empty($_SESSION["userEmail"]) ? $_SESSION["userEmail"] : "" ;?>';
    const status = '<?php echo $schedule["status"] ?>';
    const fare = parseFloat('<?php echo $schedule["fare"] ?>');
    let seats = [];
    let discount = 0;
    let totalFare = 0;

    $("#passengerType").change(function() {
        const passengerType = $(this).val();
        if (passengerType === "student" || passengerType === "senior" || passengerType === "pwd") {
            discount = 0.20;
            $("#uploadIdSection").show();
        } else {
            discount = 0;
            $("#uploadIdSection").hide();
        }
        handleTotal();
    });

    $(".btn-seat").click(function() {
        const seat = $(this).data("seat");

        if($(this).hasClass("btn-outline-dark")) {
            $(this).removeClass("btn-outline-dark").addClass("bg-dark text-white");
            seats.push(seat);
        } else {
            $(this).addClass("btn-outline-dark").removeClass("bg-dark text-white");
            seats = seats.filter(s => s !== seat);
        }

        handleTotal();
    });

    function handleTotal() {
        const discountAmount = fare * discount;
        totalFare = seats.length * fare * (1 - discount);
        $("#farePerSeat").text(fare.toFixed(2));
        $("#discountAmount").text(discountAmount.toFixed(2));
        $("#total").text(totalFare.toFixed(2));
    }

    $("#booked").click(async function() {
        if (seats.length === 0) {
            alert('Please select seat number.');
            return;
        }

        if (!passenger_id) {
            alert('Unable to create booking. Please sign in to your account.');
            return;
        }

        if (!isVerified) {
            alert('Unable to create booking. Please verify your account.');
            return;
        }

        const passengerType = $("#passengerType").val();
        const uploadIdFile = $("#uploadId")[0].files[0];

        if (passengerType !== "regular" && !uploadIdFile) {
            alert('Please upload an ID for discounted passengers.');
            return;
        }

        if (status === 'waiting') {
            let promises = [];
            for (let i = 0; i < seats.length; i++) {
                const seat = seats[i];

                let formData = new FormData();
                formData.append('type', 1);
                formData.append('schedule_id', schedule_id);
                formData.append('passenger_id', passenger_id);
                formData.append('total', totalFare);
                formData.append('seat_num', seat);
                formData.append('routeName', routeName);
                formData.append('passenger_email', passenger_email);
                formData.append('passenger_type', passengerType);

                // Append the uploaded file to the FormData
                if (uploadIdFile) {
                    formData.append('upload_id', uploadIdFile);
                }

                promises.push(
                    fetch('controllers/create-booking.php', {
                        method: 'POST',
                        body: formData,
                    })
                );
            }

            try {
                const values = await Promise.all(promises);
                console.log('values', values);
                alert("New booking added successfully!");
                window.location.href = 'account.php';
            } catch (error) {
                alert("Error on booking.");
                location.reload();
            }
        } else {
            alert('Oops! Unable to book this schedule.');
            return;
        }
    });
</script>
<?php include('includes/layout-footer.php')?>
