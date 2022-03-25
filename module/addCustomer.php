<form method="post" action="." id="dataEntryForm">
    <h1>Add Customer</h1>
    <div class="grid">
    <div class="unit two-thirds">
    <ul>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
        <li>
            <label for="customerID">Customer:</label>
            <select name="customerID" required>
                <option value="" disabled selected>Select customer</option>
                <?php
                    for($i = 0; $i < sizeof($customers); $i++){
                        echo '<option value=' . $customers[$i][0] . '>' . $customers[$i][1] . ' - ' . $customers[$i][2] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <input type="number" name="skuNumber" placeholder="Select Customer" value="<?php echo $itemData[0]?>" id="skuNumber" hidden>
        </li>

    </ul>
    </div>
    </div>
    <button type="submit" name="action" id="action" value="submitNewCustomer"><span>e</span></button>

</form>