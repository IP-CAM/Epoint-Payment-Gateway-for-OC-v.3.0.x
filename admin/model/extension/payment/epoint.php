<?php
/**
 * @pluginName epoint Opencart 3.x Payment Gateway
 * @pluginUrl https://epoint.az/
 * @varion 1.0.0
 * @author Rauf ABBASZADE <rafo.abbas@gmail.com>
 * @authorURI: https://abbasazade.dev/
 * @opencartVersion "3.0.x"
 */

class ModelExtensionPaymentEpoint extends Model
{
    public function generateTable()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "epoint`");

        $this->db->query(
            "CREATE TABLE `" . DB_PREFIX . "epoint`
                (
                    `id`         int(11)  NOT NULL,
                    `order_id`   int(11)  NOT NULL,
                    `payment_id` varchar(255) DEFAULT NULL,
                    `request`    text              DEFAULT NULL,
                    `response`   text              DEFAULT NULL,
                    `date_added` datetime NOT NULL DEFAULT current_timestamp()
                ) ENGINE = MyISAM
                  DEFAULT CHARSET = utf8;"
        );

        $this->db->query("ALTER TABLE `" . DB_PREFIX . "epoint` ADD PRIMARY KEY (`id`)");
        $this->db->query("ALTER TABLE  `" . DB_PREFIX . "epoint` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3");
    }
}