<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionsData
{
    public static function run(): void
    {
        Permission::insert([
            // crud
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Advertisement', 'عرض أي إعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Advertisement', 'عرض تفاصيل الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Advertisement', 'إنشاء إعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Advertisement', 'حذف الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Branch', 'عرض أي فرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Branch', 'عرض تفاصيل الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Branch', 'إنشاء فرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Branch', 'تحديث الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Branch', 'حذف الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Category', 'عرض أي فئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Category', 'عرض تفاصيل الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Category', 'إنشاء فئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Category', 'تحديث الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Category', 'حذف الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any City', 'عرض أي مدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View City', 'عرض تفاصيل المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create City', 'إنشاء مدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update City', 'تحديث المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete City', 'حذف المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Gift', 'عرض أي هديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Gift', 'عرض تفاصيل الهديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Gift', 'إنشاء هديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Gift', 'حذف الهديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Coupon', 'عرض أي كوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Coupon', 'عرض تفاصيل الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Coupon', 'إنشاء كوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Coupon', 'حذف الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Feedback', 'عرض أي ملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Feedback', 'عرض تفاصيل الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Feedback', 'حذف الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Support', 'عرض أي دعم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-support',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Support', 'عرض تفاصيل الدعم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-support',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Support', 'حذف الدعم']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-support',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Order', 'عرض أي طلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Order', 'عرض تفاصيل الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Order', 'إنشاء طلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Order', 'تحديث الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Order', 'حذف الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Order Archive', 'عرض أرشيف طلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Order Archive', 'عرض تفاصيل أرشيف الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Payment Method', 'عرض أي طريقة دفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Payment Method', 'عرض تفاصيل طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Payment Method', 'إنشاء طريقة دفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Payment Method', 'تحديث طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Payment Method', 'حذف طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Permission', 'عرض أي صلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Permission', 'عرض تفاصيل الصلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Permission', 'إنشاء صلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Permission', 'تحديث الصلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Permission', 'حذف الصلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Product', 'عرض أي منتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Product', 'عرض تفاصيل المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Product', 'إنشاء منتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Product', 'تحديث المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Product', 'حذف المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Role', 'عرض أي دور']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Role', 'عرض تفاصيل الدور']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Role', 'إنشاء دور']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Role', 'تحديث الدور']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Role', 'حذف الدور']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Ticket', 'عرض أي تذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Ticket', 'عرض تفاصيل التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Ticket', 'حذف التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any User', 'عرض أي مستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View User', 'عرض تفاصيل المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create User', 'إنشاء مستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update User', 'تحديث المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete User', 'حذف المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-user',
            ],

            // custom
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can Receive Order Notifications', 'يمكنه استلام إشعارات الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-receive-order-notifications',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can Be Assigned To Take Orders', 'يمكن تعيينه لأخذ الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-be-assigned-to-take-orders',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can Be Assigned To Branch', 'يمكن تعيينه للفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-be-assigned-to-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Dashboard', 'عرض لوحة التحكم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-dashboard',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Delivery Orders Page', 'عرض صفحة طلبات التوصيل']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-delivery-orders-page',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Today\'s Orders Page', 'عرض صفحة طلبات اليوم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-todays-orders-page',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Today\'s Tickets Page', 'عرض صفحة التذاكر اليوم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-todays-tickets-page',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Manage Settings', 'إدارة الإعدادات']), JSON_UNESCAPED_UNICODE),
                'code' => 'manage-settings',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Manage Developer Settings', 'إدارة إعدادات المطور']), JSON_UNESCAPED_UNICODE),
                'code' => 'manage-developer-settings',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Mark Default Branch', 'تعيين فرع افتراضي']), JSON_UNESCAPED_UNICODE),
                'code' => 'mark-default-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unmark Default Branch', 'إلغاء تعيين فرع افتراضي']), JSON_UNESCAPED_UNICODE),
                'code' => 'unmark-default-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Branch', 'نشر الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Branch', 'إلغاء نشر الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Advertisement', 'نشر الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Filter Branch Content', 'تصفية محتوى الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'filter-branch-content',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Advertisement', 'إلغاء نشر الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Payment Method', 'نشر طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Payment Method', 'إلغاء نشر طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Product', 'نشر المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Coupon', 'نشر الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Coupon', 'إلغاء نشر الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Show Code Coupon', 'عرض كود الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'show-code-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Gift', 'نشر الهديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Gift', 'إلغاء نشر الهديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Show Code Gift', 'عرض كود الهديه']), JSON_UNESCAPED_UNICODE),
                'code' => 'show-code-gift',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Category', 'نشر الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Category', 'إلغاء نشر الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Products Category', 'عرض منتجات الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-products-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Categories Category', 'عرض فئات الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-categories-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Cancel Order', 'إلغاء الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'cancel-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Approve Order', 'الموافقة على الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'approve-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Force Approve Order', 'فرض الموافقة على الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'force-approve-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Select Delivery Order', 'اختيار توصيل الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'select-delivery-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Finish Order', 'إنهاء الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'finish-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Close Order', 'إغلاق الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'close-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Archive Order', 'أرشفة الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'archive-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Invoice Order', 'عرض فاتورة الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-invoice-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can View Audit', 'يستطيع عرض سجلات المراقبه']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-view-audit',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Block User', 'حظر المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'block-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unblock User', 'إلغاء حظر المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'unblock-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Approve User', 'الموافقة على المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'approve-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Disapprove User', 'رفض المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'disapprove-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['send notification', 'ارسال الاشعارات']), JSON_UNESCAPED_UNICODE),
                'code' => 'send-notification',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Mark Important Feedback', 'تعيين ملاحظة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'mark-important-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unmark Important Feedback', 'إلغاء تعيين ملاحظة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unmark-important-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Process Feedback', 'معالجة الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'process-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Transfer feedback to Another Branch', 'نقل الملاحظة إلى فرع آخر']), JSON_UNESCAPED_UNICODE),
                'code' => 'change-branch-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Mark Important Ticket', 'تعيين تذكرة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'mark-important-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unmark Important Ticket', 'إلغاء تعيين تذكرة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unmark-important-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Process Ticket', 'معالجة التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'process-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Transfer Ticket to Another Branch', 'نقل التذكرة إلى فرع آخر']), JSON_UNESCAPED_UNICODE),
                'code' => 'change-branch-ticket',
            ],
        ]);
    }
}
