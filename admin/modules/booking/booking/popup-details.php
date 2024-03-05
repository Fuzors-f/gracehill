<html>
<?php
define("ADMIN", true);

define("SYSBASE", str_replace("\\", "/", realpath(dirname(__FILE__)."/../../../../")."/"));

require_once(SYSBASE."common/lib.php");
require_once(SYSBASE."common/define.php");

if(!isset($_SESSION['user'])) exit();
elseif($_SESSION['user']['type'] == "registered"){
    unset($_SESSION['user']);
    exit();
}

if(isset($_POST['id']) && isset($_SESSION['user']['id'])){
    $id_booking = (int)$_POST['id'];
    if(is_numeric($id_booking)){ ?>
        <head>
        <script>
            function printElem(elem, elem2){
                var popup = window.open('', 'print', 'height=800,width=600');
                popup.document.write('<html><head><title>'+document.title+'</title><link rel="stylesheet" href="<?php echo DOCBASE.ADMIN_FOLDER.'/css/print.css'; ?>"/></head><style>@media print{.pages{page-break-after: always;}}</style><body><div class="pages">'+document.getElementById(elem).innerHTML+'</div><div class="pages">'+document.getElementById(elem2).innerHTML+'</div></body></html>');
                setTimeout(function(){ 
                    popup.document.close();
                    popup.focus();
                    popup.print();
                    popup.close();    
                }, 600);


                
                return true;
            }
        </script>
        <style>
             .white-popup-block {
                 width:850px;
                 max-width:none;
            }
        </style>
        </head>
        <body>
          
        <div class="white-popup-block" id="popup-booking-<?php echo $id_booking; ?>">
            <?php
            $result_booking = $db->query('SELECT * FROM pm_booking WHERE id = '.$id_booking);
            if($result_booking !== false && $db->last_row_count() > 0){
                
                $row = $result_booking->fetch();
            
                echo '
                <img src="../../../images/logo-admin.png" height="100px"> <br>
                <h4>'.ADDRESS.'</h4> <br>
                <h4>'.PHONE.'</h4> <br><br>
                <h5> Invoice no :  #'.$id_booking.'</h5>
                <a href="index.php?view=form&id='.$id_booking.'" class="pull-right action-btn"><i class="fas fa-fw fa-edit"></i></a>
                <a href="#" onclick="javascript:printElem(\'popup-booking-'.$id_booking.'\',\'rules\');return false;" class="pull-right action-btn"><i class="fas fa-fw fa-print"></i></a>
                
                <table class="table table-responsive table-bordered">
                    <tr>
                        <th width="50%">'.$texts['BOOKING_DETAILS'].'</th>
                        <th width="50%">'.$texts['BILLING_ADDRESS'].'</th>
                    </tr>
                    <tr>
                        <td>
                            '.$texts['CHECK_IN'].' <strong>'.gmstrftime(DATE_FORMAT, $row['from_date']).'</strong><br>
                            '.$texts['CHECK_OUT'].' <strong>'.gmstrftime(DATE_FORMAT, $row['to_date']).'</strong><br>
                            <strong>'.$row['nights'].'</strong> '.$texts['NIGHTS'].'<br>
                            <strong>'.($row['adults']+$row['children']).'</strong> '.$texts['PERSONS'].' - 
                            '.$texts['ADULTS'].': <strong>'.$row['adults'].'</strong> / 
                            '.$texts['CHILDREN'].': <strong>'.$row['children'].'</strong>';
                            if($row['comments'] != '') echo '<p><b>'.$texts['COMMENTS'].'</b><br>'.nl2br($row['comments']).'</p>';
                            echo '
                        </td>
                        <td>
                            '.$row['firstname'].' '.$row['lastname'].'<br>';
                            if($row['company'] != '') echo $texts['COMPANY'].' : '.$row['company'].'<br>';
                            echo nl2br($row['address']).'<br>
                            '.$row['postcode'].' '.$row['city'].'<br>
                            '.$texts['PHONE'].' : '.$row['phone'].'<br>';
                            if($row['mobile'] != '') echo $texts['MOBILE'].' : '.$row['mobile'].'<br>';
                            echo $texts['EMAIL'].' : '.$row['email'].'
                        </td>
                    </tr>
                </table>';
                
                $result_room = $db->query('SELECT * FROM pm_booking_room WHERE id_booking = '.$row['id']);
                if($result_room !== false && $db->last_row_count() > 0){
                    echo '
                    <table class="table table-responsive table-bordered">
                        <tr>
                            <th>'.$texts['ROOM'].'</th>
                            <th>'.$texts['PERSONS'].'</th>
                            <th class="text-center">'.$texts['TOTAL'].'</th>
                        </tr>';
                        foreach($result_room as $room){
                            echo
                            '<tr>
                                <td>'.$room['title'].'</td>
                                <td>
                                    '.($room['adults']+$room['children']).' '.getAltText($texts['PERSON'], $texts['PERSONS'], ($room['adults']+$room['children'])).' (';
                                    if($room['adults'] > 0) echo $room['adults'].' '.getAltText($texts['ADULT'], $texts['ADULTS'], $room['adults']).' ';
                                    if($room['children'] > 0) echo $room['children'].' '.getAltText($texts['CHILD'], $texts['CHILDREN'], $room['children']).' ';
                                    echo ')
                                </td>
                                <td class="text-right" width="15%">'.formatPrice($room['amount']*CURRENCY_RATE).'</td>
                            </tr>';
                        }
                        echo '
                    </table>';
                }
                
                $result_service = $db->query('SELECT * FROM pm_booking_service WHERE id_booking = '.$row['id']);
                if($result_service !== false && $db->last_row_count() > 0){
                    echo '
                    <table class="table table-responsive table-bordered">
                        <tr>
                            <th>'.$texts['SERVICES'].'</th>
                            <th>'.$texts['QUANTITY'].'</th>
                            <th class="text-center">'.$texts['TOTAL'].'</th>
                        </tr>';
                        foreach($result_service as $service){
                            echo
                            '<tr>
                                <td>'.$service['title'].'</td>
                                <td>'.$service['qty'].'</td>
                                <td class="text-right" width="15%">'.formatPrice($service['amount']*CURRENCY_RATE).'</td>
                            </tr>';
                        }
                        echo '
                    </table>';
                }
                
                $result_activity = $db->query('SELECT * FROM pm_booking_activity WHERE id_booking = '.$row['id']);
                if($result_activity !== false && $db->last_row_count() > 0){
                    echo '
                    <table class="table table-responsive table-bordered">
                        <tr>
                            <th>'.$texts['ACTIVITY'].'</th>
                            <th>'.$texts['DURATION'].'</th>
                            <th>'.$texts['DATE'].'</th>
                            <th>'.$texts['PERSONS'].'</th>
                            <th class="text-center">'.$texts['TOTAL'].'</th>
                        </tr>';
                        foreach($result_activity as $activity){
                            echo
                            '<tr>
                                <td>'.$activity['title'].'</td>
                                <td>'.$activity['duration'].'</td>
                                <td>'.gmstrftime(DATE_FORMAT.' '.TIME_FORMAT, $activity['date']).'</td>
                                <td>
                                    '.($activity['adults']+$activity['children']).' '.getAltText($texts['PERSON'], $texts['PERSONS'], ($activity['adults']+$activity['children'])).' (';
                                    if($activity['adults'] > 0) echo $activity['adults'].' '.getAltText($texts['ADULT'], $texts['ADULTS'], $activity['adults']).' ';
                                    if($activity['children'] > 0) echo $activity['children'].' '.getAltText($texts['CHILD'], $texts['CHILDREN'], $activity['children']).' ';
                                    echo ')
                                </td>
                                <td class="text-right" width="15%">'.formatPrice($activity['amount']*CURRENCY_RATE).'</td>
                            </tr>';
                        }
                        echo '
                    </table>';
                }
                echo '
                <table class="table table-responsive table-bordered">';
               
                    /*if(ENABLE_TOURIST_TAX == 1 && $row['tourist_tax'] > 0){
                        echo '
                        <tr>
                            <th class="text-right">'.$texts['TOURIST_TAX'].'</th>
                            <td class="text-right">'.formatPrice($row['tourist_tax']*CURRENCY_RATE).'</td>
                        </tr>';
                    }*/
                    
                    if(isset($row['discount_amount']) && $row['discount_amount'] > 0){
                        echo '
                        <tr>
                            <th class="text-right">'.$texts['DISCOUNT'].'</th>
                            <td class="text-right">- '.formatPrice($row['discount_amount']*CURRENCY_RATE).'</td>
                        </tr>';
                    }
                    
                    $result_tax = $db->query('SELECT * FROM pm_booking_tax WHERE id_booking = '.$row['id']);
                    if($result_tax !== false && $db->last_row_count() > 0){
                        foreach($result_tax as $tax){
                            echo '
                            <tr>
                                <th class="text-right">'.$tax['name'].'</th>
                                <td class="text-right">'.formatPrice($tax['amount']*CURRENCY_RATE).'</td>
                            </tr>';
                        }
                    }
                    
                    echo '
                    <tr>
                        <th class="text-right">'.$texts['TOTAL'].'</th>
                        <td class="text-right" width="15%"><b>'.formatPrice($row['total']*CURRENCY_RATE).'</b></td>
                    </tr>';
                    
                    if(ENABLE_DOWN_PAYMENT == 1 && $row['down_payment'] > 0){
                        echo '
                        <tr>
                            <th class="text-right">'.$texts['DOWN_PAYMENT'].'</th>
                            <td class="text-right" width="15%"><b>'.formatPrice($row['down_payment']*CURRENCY_RATE).'</b></td>
                        </tr>';
                    }
                    echo '
                </table>';
                    
                echo '<p><strong>'.$texts['PAYMENT'].'</strong><p>';
                
                echo '<p>'.$texts['PAYMENT_METHOD'].' : '.$row['payment_option'].'<br>';
                echo $texts['STATUS'].': ';
                switch($row['status']){
                    case 1: echo $texts['PENDING']; break;
                    case 2: echo $texts['CANCELLED']; break;
                    case 3: echo $texts['REJECTED_PAYMENT']; break;
                    case 4: echo $texts['PAID']; break;
                    default: echo $texts['PENDING']; break;
                }
                echo '<br>';
                
                $result_payment = $db->query('SELECT * FROM pm_booking_payment WHERE id_booking = '.$row['id']);
				if($result_payment !== false && $db->last_row_count() > 0){
					echo '
					<table class="table table-responsive table-bordered">
						<tr>
							<th>'.$texts['DATE'].'</th>
							<th>'.$texts['PAYMENT_METHOD'].'</th>
							<th class="text-center">'.$texts['AMOUNT'].'</th>
						</tr>';
						foreach($result_payment as $payment){
							echo
							'<tr>
								<td>'.gmstrftime(DATE_FORMAT.' '.TIME_FORMAT, $payment['date']).'</td>
								<td>'.$payment['method'].'</td>
								<td class="text-right" width="15%">'.formatPrice($payment['amount']*CURRENCY_RATE).'</td>
							</tr>';
						}
						echo '
					</table>';
				}
                
                if($row['status'] == 4){
                    if(!empty($row['payment_date'])) echo $texts['PAYMENT_DATE'].' : '.gmstrftime(DATE_FORMAT.' '.TIME_FORMAT, $row['payment_date']).'<br>';
                    if(!empty($row['down_payment'])) echo $texts['DOWN_PAYMENT'].' : '.formatPrice($row['down_payment']*CURRENCY_RATE).'<br>';
                    if(!empty($row['trans'])) echo $texts['NUM_TRANSACTION'].' : '.$row['trans'].'<br>';
                    echo '<b>'.$texts['BALANCE'].' : '.formatPrice(($row['total']-$row['down_payment'])*CURRENCY_RATE).'</b>';
                }else
                    echo '<b>'.$texts['BALANCE'].' : '.formatPrice($row['total']*CURRENCY_RATE).'</b><br>';
                echo '</p>';
            } ?>
        </div>


        <div id="rules" style="display: none;">
        <p>
            <strong>GRACE HILL VILLA</strong>
            <strong></strong>
        </p>
        <p>
            <strong>RESERVATIONS TERMS AND CONDITIONS</strong>
        </p>
        <p>
            Nama Penginap : <?php echo $row['firstname'].' '.$row['lastname'];?>
        </p>
        <p>
            Pribadi/keluarga/Group/Insansi:
        </p>
        <p>
            No. Hp. :
        </p>
        <p>
            KTP:
        </p>
        <p>
            <strong>
                Dengan melanjutkan reservasi ini, Anda menerima dan setuju dengan GRACE
                HILL VILLA bahwa reservasi, akan tunduk pada syarat dan ketentuan
                sebagai berikut:
            </strong>
        </p>
        <p>
            <strong>USIA YANG SAH UNTUK CHECK-IN</strong>
            <br/>
            • Anda harus berusia minimal 18 tahun untuk check-in dan memesan kamar.
        </p>
        <p>
            <strong>WAKTU CHECK-IN DAN CHECK-OUT </strong>
            <br/>
            • Waktu check-in adalah dari 14.00 dan waktu check-out terakhir adalah
            12.00.
            <br/>
            • Diperlukan tanda pengenal resmi atau paspor yang berlaku saat check-in.
        </p>
        <p>
            <strong>Larangan</strong>
            <br/>
            • Tidak diperboleh merokok didalam ruangan dan kamar VILLA akan dikenakan
            denda 1(satu) juta rp.
            <br/>
            • Tidak diizinkan mengadakan kumpulan atau pesta MIRAS(minuman keras) jenis
            apa pun.
            <br/>
            • Tidak diizinkan mengadakan penggunaan NARKOBA(obat-obat terlarang).
            <br/>
            • Tidak diizinkan didalam kamar pasangan yang belum menikah(asusila).
        </p>
        <p>
            • Tidak diizinkan PESTA JUDI.
        </p>
        <p>
            • Tidak diizinkan PESTA SEX(LGBT).
        </p>
        <p>
            •
            <strong>
                <u>
                    Tidak diizinkan penambahan tamu.Villa berhak meminta penambahan
                    biaya/tamu (150rb/org).
                </u>
            </strong>
            <br/>
            • Reservasi ini tidak dapat dialihkan. Tidak diperkenankan untuk melakukan
            perubahan nama.
        </p>
        <p>
            <strong>KETENTUAN UMUM</strong>
            <br/>
            • Dengan melanjutkan ke reservasi, berarti Anda setuju dan mengetahui bahwa
            jika reservasi ini diterima oleh Villa, maka selanjutnya masa tinggal Anda
            tunduk pada syarat dan ketentuan standar tinggal di Villa ini.
            <br/>
            • Villa ini dapat, berdasarkan kebijaksaan mutlaknya, membatalkan reservasi
            ini, jika Villa berpendapat bahwa informasi yang diberikan dalam pemesanan
            ini tidak benar atau tidak lengkap.
            <br/>
            • Villa berhak mengubah, mengamandemen, dan/atau mengganti syarat dan
            ketentuan ini kapan saja tanpa pemberitahuan sebelumnya.
            <br/>
            • Anda harus mengganti rugi dan melepaskan Villa dari tanggung jawab
            sehubungan dengan kewajiban, kerugian, kerusakan, biaya, dan pengeluaran
            apa pun yang disebabkan oleh, dan/atau sehubungan dengan, penerimaan
            reservasi oleh Villa.
            <br/>
            • Villa tidak akan bertanggung jawab dari segala kerugian, kerusakan,
            biaya, atau pengeluaran yang muncul dari pembatalan reservasi oleh Villa.
            <br/>
            • Reservasi dan syarat dan ketentuan yang diuraikan di sini diatur sesuai
            undang-undang Republik Indonesia.
            <br/>
            <br/>
        </p>
        <p>
            Disetujui,
        </p>
        <br><br><br><br>
        <p>
            (   <?php  echo '     ' .$row['firstname'].' '.$row['lastname'].'     ' ?>)
        </p>
    

        </div>

        </body>
        <?php
    }
} ?>
</html>
