<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/dashboard.css">
    </head>

<body>

     <div id = "dashboardname" class="row border border-white">
                <center><h1><b>LABAssistance</b></h1>
                <label>Laundry Management System</label>
                </center>
            </div>
<div class="main">
    <!-- MAIN -->
    <a class="mt-3 btn btn-dark w-50" href="login.php">Log Out</a>

    <button class="mt-3 btn btn-dark w-50" onclick="showNewOrder()">Place New Order</button>

   <a class="mt-3 btn btn-dark w-50" href="login.php">History</a>

    <!-- New Order -->
    <div id="newOrder" >
        <center><h1><b>Place an Order</b></h1></center>
        <center><label>1 Load = Min 3kg, Mac 8kg</label></center>

        <h5 id="optionslabel" class="mt-3"><b>Service Type</b></h5>
        <div class="agreecont">
                <label class="smooth-checkbox">
                    <input type="checkbox">
                    <span class="checkmark"></span>
                    Wash (₱50/load)
                    </label>
                </div>

        <div class="agreecont">
                <label class="smooth-checkbox">
                    <input type="checkbox">
                    <span class="checkmark"></span>
                    Dry (₱60/load)
                    </label>
                </div>

        <div class="agreecont">
                <label class="smooth-checkbox">
                    <input type="checkbox">
                    <span class="checkmark"></span>
                    Fold (₱35/load)
                    </label>
                </div>

        <h5 id="optionslabel" class="mt-3"><b>Additional Laundry Supplies (for Wash)</b></h5>
         <div class="agreecont">
                <label class="smooth-checkbox">
                    <input type="checkbox">
                    <span class="checkmark"></span>
                    Detergent (₱20/load)
                    </label>
                </div>
       
                <div class="agreecont">
                <label class="smooth-checkbox">
                    <input type="checkbox">
                    <span class="checkmark"></span>
                    Detergent (₱20/load)
                    </label>
                </div>

        <h5 id="optionslabel" class="mt-3"><b>Load Quantity</b></h5>
        <labe>Enter the number of bags/basket per category</label>
        <label>1 bag/basket = 1 load</label>
        <div id = "clotheclass" class="d-flex gap-5">
            <div class="row w-50 custom-input-group">
                <label><b>Colored</b></label>
                <input type="number" class="smooth-input">
            </div>
            <div class="row w-50 custom-input-group">
                <label><b>White</b></label>
                <input type="number" class="smooth-input">
            </div>
        </div>

        <div class="row border border-white">
              <h5 id="optionslabel" class="mt-3"><b>Note to Employee</b></h5>
            <textarea id="notearea" name="note" placeholder="Example: Keep uniforms inside net"></textarea>
        </div>


        <h2 class="totalpayment"><b>Total Payment</b></h2>
        <d id="price">₱0</d>
        <h6>Payment to be made in-shop after order completion</h6>
        <h6 id = "read">*Note: When weight exceeds the maximum for each load (8kg), the excess laundry will be counted towards a new load and final payment will be adjusted accordingly</h6>

        <center><button type="submit" class="mt-3 btn btn-dark w-75">Place Order</button></center>

        <center><button class="mt-3 btn btn-dark w-75" onclick="hideNewOrder()">Cancel</button></center>
    </div>

    </div>

    <!-- FOOTER -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>