<form method="post" action="." id="dataEntryForm">
    <h1>Add Vendor</h1>
    <div class="grid">
    <div class="unit two-thirds">
    <ul>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
        <li>
            <label for="vendorID">Vendor:</label>
            <select name="vendorID" required>
                <option value="" disabled selected>Select vendor</option>
                <?php
                    for($i = 0; $i < sizeof($vendors); $i++){
                        echo '<option value=' . $vendors[$i][0] . '>' . $vendors[$i][1] . ' - ' . $vendors[$i][2] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <input type="number" name="skuNumber" value="<?php echo $itemData[0]?>" id="skuNumber" hidden>
        </li>

    </ul>
    </div>
    </div>
    <button type="submit" name="action" id="action" value="submitNewVendor"><span>e</span></button>

</form>