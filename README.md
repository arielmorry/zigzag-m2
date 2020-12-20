# ZigZag Shipping Module for Magento 2

####**Configuration (Base):**

**Username**: username provided by ZigZag

**Password**: password provided by ZigZag

**Email To Notify About Errors**: email address to send errors in case of failed shipment.

**Enabled Shipping Types By ZigZag**: (view only) All available shipping types ZigZag has enabled to client (will be refreshed after saving configuration)

####**Configuration (Regular/Double/Reverse):**

**Enabled**: Enable/Disable shipping method in frontend

**Title**: Shipping Method Title (e.g. זיגזג)

**Method Name**: Shipping Method Name  (e.g. שליח עד הבית)

**Minimum Order Amount**: Minimum Order Amount to show shipping method in frontend

**Shipping Cost**: How much the delivery costs

**Auto Shipment for Specific Order Statuses After Order is Placed**: 

If set to "Yes" the module will create shipment to all orders and will submit them to ZigZag systems.

If set to "No" the only way to create a shipment in ZigZag is manually via admin panel > sales order view 

**Send Shipment To ZigZag For Order Statuses**: If Auto Shipment sets to "Yes", this field specifies for which order statuses the automatic process will take place 