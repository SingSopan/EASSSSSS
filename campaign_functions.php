<?php
// This function updates the campaign stats based on transactions
function updateCampaignStats($conn, $campaign_id) {
    try {
        $conn->begin_transaction();

        // Calculate total amount from all completed transactions
        $amount_stmt = $conn->prepare("
            UPDATE campaigns c 
            SET current_amount = (
                SELECT COALESCE(SUM(amount), 0) 
                FROM transactions 
                WHERE campaign_id = ? 
                AND status = 'completed'
            )
            WHERE id = ?
        ");
        $amount_stmt->bind_param("ii", $campaign_id, $campaign_id);
        $amount_stmt->execute();

        // Update backer count with count of unique donors
        $backer_stmt = $conn->prepare("
            UPDATE campaigns c 
            SET backer_count = (
                SELECT COUNT(DISTINCT user_id) 
                FROM transactions 
                WHERE campaign_id = ? 
                AND status = 'completed'
            )
            WHERE id = ?
        ");
        $backer_stmt->bind_param("ii", $campaign_id, $campaign_id);
        $backer_stmt->execute();

        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating campaign stats: " . $e->getMessage());
        return false;
    }
}
?>