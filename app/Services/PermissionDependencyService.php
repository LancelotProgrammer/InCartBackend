<?php

namespace App\Services;

use App\Models\Permission;

class PermissionDependencyService
{
    protected static array $errorDependencies = [

        /*
            CRUD
        */

        'view-any-advertisement',
        'view-advertisement' => ['view-any-advertisement'],
        'create-advertisement' => ['view-any-advertisement'],
        'delete-advertisement' => ['view-any-advertisement'],

        'view-any-branch',
        'view-branch' => ['view-any-branch'],
        'create-branch' => ['view-any-branch'],
        'update-branch' => ['view-any-branch'],
        'delete-branch' => ['view-any-branch'],

        'view-any-category',
        'view-category' => ['view-any-category'],
        'create-category' => ['view-any-category'],
        'update-category' => ['view-any-category'],
        'delete-category' => ['view-any-category'],

        'view-any-city',
        'view-city' => ['view-any-city'],
        'create-city' => ['view-any-city'],
        'update-city' => ['view-any-city'],
        'delete-city' => ['view-any-city'],

        'view-any-gift',
        'view-gift' => ['view-any-gift'],
        'create-gift' => ['view-any-gift'],
        'delete-gift' => ['view-any-gift'],

        'view-any-coupon',
        'view-coupon' => ['view-any-coupon'],
        'create-coupon' => ['view-any-coupon'],
        'delete-coupon' => ['view-any-coupon'],

        'view-any-feedback',
        'view-feedback' => ['view-any-feedback'],
        'delete-feedback' => ['view-any-feedback'],

        'view-any-support',
        'view-support' => ['view-any-support'],
        'delete-support' => ['view-any-support'],

        'view-any-order',
        'view-order' => ['view-any-order'],
        'create-order' => ['view-any-order'],
        'update-order' => ['view-any-order'],
        'delete-order' => ['view-any-order'],

        'view-any-order-archive',
        'view-order-archive' => ['view-any-order-archive'],

        'view-any-payment-method',
        'view-payment-method' => ['view-any-payment-method'],
        'create-payment-method' => ['view-any-payment-method'],
        'update-payment-method' => ['view-any-payment-method'],
        'delete-payment-method' => ['view-any-payment-method'],

        'view-any-permission',
        'view-permission' => ['view-any-permission'],
        'create-permission' => ['view-any-permission'],
        'update-permission' => ['view-any-permission'],
        'delete-permission' => ['view-any-permission'],

        'view-any-product',
        'view-product' => ['view-any-product'],
        'create-product' => ['view-any-product'],
        'update-product' => ['view-any-product'],
        'delete-product' => ['view-any-product'],

        'view-any-role',
        'view-role' => ['view-any-role'],
        'create-role' => ['view-any-role'],
        'update-role' => ['view-any-role'],
        'delete-role' => ['view-any-role'],

        'view-any-ticket',
        'view-ticket' => ['view-any-ticket'],
        'delete-ticket' => ['view-any-ticket'],

        'can-view-audit',

        'view-any-user',
        'view-user' => ['view-any-user'],
        'create-user' => ['view-any-user'],
        'update-user' => ['view-any-user'],
        'delete-user' => ['view-any-user'],

        /*
            custom CRUD actions
        */

        'show-code-gift' => ['view-any-gift'],
        'show-code-coupon' => ['view-any-coupon'],

        'view-products-category' => ['view-any-category'],
        'view-categories-category' => ['view-any-category'],

        'publish-branch' => ['view-any-branch'],
        'unpublish-branch' => ['view-any-branch'],
        'publish-advertisement' => ['view-any-advertisement'],
        'unpublish-advertisement' => ['view-any-advertisement'],
        'publish-payment-method' => ['view-any-payment-method'],
        'unpublish-payment-method' => ['view-any-payment-method'],
        'publish-coupon' => ['view-any-coupon'],
        'unpublish-coupon' => ['view-any-coupon'],
        'publish-gift' => ['view-any-gift'],
        'unpublish-gift' => ['view-any-gift'],
        'publish-category' => ['view-any-category'],
        'unpublish-category' => ['view-any-category'],
        'publish-city' => ['view-any-city'],
        'unpublish-city' => ['view-any-city'],

        'publish-product' => ['update-product'],

        'block-user' => ['view-any-user'],
        'unblock-user' => ['view-any-user'],
        'approve-user' => ['view-any-user'],
        'disapprove-user' => ['view-any-user'],
        'impersonate-user' => ['view-any-user'],
        'send-notification' => ['view-any-user'],

        'mark-default-branch' => ['view-any-branch'],
        'unmark-default-branch' => ['view-any-branch'],
        'mark-important-feedback' => ['view-any-feedback'],
        'unmark-important-feedback' => ['view-any-feedback'],
        'mark-important-ticket' => ['view-any-ticket'],
        'unmark-important-ticket' => ['view-any-ticket'],

        'process-feedback' => ['view-any-feedback'],
        'change-branch-feedback' => ['view-any-feedback'],
        'process-ticket' => ['view-any-ticket'],
        'change-branch-ticket' => ['view-any-ticket'],

        /*
            custom actions
        */

        'manage-developer-settings', // for developer only
        'view-dashboard',
        'manage-settings',
        'view-todays-orders-page',
        'view-todays-tickets-page',

        'can-be-assigned-to-branch' => ['filter-branch-content'], // for employee only
        'filter-branch-content' => ['can-be-assigned-to-branch'], // for employee only

        'can-be-assigned-to-take-orders' => ['view-delivery-orders-page'], // for delivery only
        'view-delivery-orders-page' => ['can-be-assigned-to-take-orders'], // for delivery only

        'can-receive-order-notifications' => [
            'view-any-order',
            'approve-order',
            'force-approve-order',
            'cancel-order',
            'update-order',
            'select-delivery-order',
            'finish-order',
            'close-order',
        ],
        'approve-order' => [
            'view-any-order',
            'can-receive-order-notifications',
            'force-approve-order',
            'cancel-order',
            'update-order',
            'select-delivery-order',
            'finish-order',
            'close-order',
        ],
        'force-approve-order' => [
            'view-any-order',
            'can-receive-order-notifications',
            'approve-order',
            'cancel-order',
            'update-order',
            'select-delivery-order',
            'finish-order',
            'close-order',
        ],
        'cancel-order' => [
            'view-any-order',
            'can-receive-order-notifications',
            'approve-order',
            'force-approve-order',
            'update-order',
            'select-delivery-order',
            'finish-order',
            'close-order',
        ],
        'select-delivery-order' => [
            'view-any-order',
            'can-receive-order-notifications',
            'approve-order',
            'force-approve-order',
            'cancel-order',
            'update-order',
            'finish-order',
            'close-order',
        ],
        'finish-order',
        'close-order' => [
            'view-any-order',
            'can-receive-order-notifications',
            'approve-order',
            'force-approve-order',
            'cancel-order',
            'update-order',
            'select-delivery-order',
            'finish-order',
        ],
        'archive-order' => [
            'view-any-order'
        ],
        'view-invoice-order' => [
            'view-any-order'
        ],
    ];

    protected static array $errorConflicts = [
        'view-any-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'can-receive-order-notifications' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'approve-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'force-approve-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'cancel-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'update-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'select-delivery-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'close-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'archive-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],
        'view-invoice-order' => [
            'can-be-assigned-to-take-orders',
            'view-delivery-orders-page',
        ],

        'can-be-assigned-to-take-orders' => ['*:view-delivery-orders-page,can-be-assigned-to-branch,filter-branch-content,finish-order'],
        'view-delivery-orders-page' => ['*:can-be-assigned-to-take-orders,can-be-assigned-to-branch,filter-branch-content,finish-order'],
    ];

    protected static array $warningMissing = [
        'filter-branch-content',
    ];

    protected static array $warningSensitive = [
        'view-dashboard',
        'manage-settings',
        'view-any-city',
        'view-any-branch',
        'view-any-role',
        'view-any-user',
        'can-view-audit',
    ];

    protected static array $infoRole = [
        'can-be-assigned-to-take-orders',
        'can-receive-order-notifications',
    ];

    public static function getDependencies(): array
    {
        return self::$errorDependencies;
    }

    public static function getConflicts(): array
    {
        return self::$errorConflicts;
    }

    public static function getWarningSensitive(): array
    {
        return self::$warningSensitive;
    }

    public static function getWarningMissing(): array
    {
        return self::$warningMissing;
    }

    public static function getWarningRole(): array
    {
        return self::$infoRole;
    }

    public static function validate(array $permissions): array
    {
        $selected = Permission::query()
            ->whereIn('id', $permissions)
            ->pluck('code')
            ->toArray();

        $errors = [];
        $warnings = [];
        $info = [];

        $allDependencies  = self::getDependencies();
        $allConflicts     = self::getConflicts();
        $warningMissing   = self::getWarningMissing();
        $warningSensitive = self::getWarningSensitive();
        $warningRole      = self::getWarningRole();

        /*
        |--------------------------------------------------------------------------
        | Check Dependency Errors
        |--------------------------------------------------------------------------
        */

        foreach ($selected as $code) {
            if (isset($allDependencies[$code])) {
                foreach ($allDependencies[$code] as $dependency) {
                    if (!in_array($dependency, $selected)) {
                        $errors[] = "{$code} requires {$dependency}";
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Check Conflict Errors
        |--------------------------------------------------------------------------
        */

        foreach ($selected as $code) {
            if (! isset($allConflicts[$code])) {
                continue;
            }

            foreach ($allConflicts[$code] as $conflict) {

                // wildcard rule: "*:perm1,perm2"
                if (str_starts_with($conflict, '*:')) {
                    // allowed list after the colon (can be comma separated)
                    $allowedString = substr($conflict, 2);
                    $allowed = array_filter(array_map('trim', explode(',', $allowedString)));

                    foreach ($selected as $other) {
                        if ($other === $code) {
                            continue; // skip self
                        }

                        // if $other is NOT in allowed list, it's a conflict
                        if (! in_array($other, $allowed, true)) {
                            $errors[] = "{$code} cannot be used with {$other}";
                        }
                    }

                    // done processing this wildcard entry
                    continue;
                }

                // normal conflict: "A conflicts with B"
                if (in_array($conflict, $selected, true)) {
                    $errors[] = "{$code} conflicts with {$conflict}";
                }
            }
        }

        // dedupe errors (optional but helpful)
        $errors = array_values(array_unique($errors));

        /*
        |--------------------------------------------------------------------------
        | Check Missing Recommendation Warnings
        |--------------------------------------------------------------------------
        */

        foreach ($warningMissing as $missing) {
            if (!in_array($missing, $selected)) {
                $warnings[] = "It is recommended to include {$missing}";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Check Sensitive Info
        |--------------------------------------------------------------------------
        */

        foreach ($selected as $code) {
            if (in_array($code, $warningSensitive)) {
                $info[] = "{$code} is a sensitive permission";
            }
        }

        foreach ($selected as $code) {
            if (in_array($code, $warningRole)) {
                $info[] = "{$code} is an order-cycle permission";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Final Message Logic
        |--------------------------------------------------------------------------
        */

        if (!empty($errors)) {

            $requires = [];
            $conflicts = [];
            $cannot = [];

            foreach ($errors as $error) {

                // requires ex: "finish-order requires view-order"
                if (str_contains($error, ' requires ')) {
                    [$p, $d] = explode(' requires ', $error, 2);
                    $requires[$p][] = $d;
                    continue;
                }

                // conflicts ex: "finish-order conflicts with cancel-order"
                if (str_contains($error, ' conflicts with ')) {
                    [$p, $d] = explode(' conflicts with ', $error, 2);
                    $conflicts[$p][] = $d;
                    continue;
                }

                // cannot be used with ex: "per-1 cannot be used with per-2"
                if (str_contains($error, ' cannot be used with ')) {
                    [$p, $d] = explode(' cannot be used with ', $error, 2);
                    $cannot[$p][] = $d;
                    continue;
                }
            }

            // Build final message
            $body = "Permission assignment failed:<br><br>";

            if (!empty($requires)) {
                $body .= "Required permissions errors:<br>";
                foreach ($requires as $perm => $deps) {
                    $body .= "• {$perm} requires:<br>";
                    foreach ($deps as $dep) {
                        $body .= "&nbsp;&nbsp;&nbsp;- {$dep}<br>";
                    }
                }
                $body .= "<br>";
            }

            if (!empty($conflicts)) {
                $body .= "Conflicting permissions errors:<br>";
                foreach ($conflicts as $perm => $deps) {
                    $body .= "• {$perm} conflicts with:<br>";
                    foreach ($deps as $dep) {
                        $body .= "&nbsp;&nbsp;&nbsp;- {$dep}<br>";
                    }
                }
                $body .= "<br>";
            }

            if (!empty($cannot)) {
                $body .= "Not allowed together errors:<br>";
                foreach ($cannot as $perm => $deps) {
                    $body .= "• {$perm} cannot be used with:<br>";
                    foreach ($deps as $dep) {
                        $body .= "&nbsp;&nbsp;&nbsp;- {$dep}<br>";
                    }
                }
                $body .= "<br>";
            }

            return [
                'type'  => 'error',
                'title' => 'Error',
                'body'  => trim($body),
            ];
        }

        if (!empty($warnings)) {

            $formatted = "Some issues were found:<br><br>";
            $formatted .= "• Recommended missing permissions:<br>";
            foreach ($warnings as $warn) {
                if (str_contains($warn, 'include')) {
                    $permission = trim(str_replace('It is recommended to include ', '', $warn));
                    $formatted .= "&nbsp;&nbsp;&nbsp;- {$permission}<br>";
                } else {
                    $formatted .= "&nbsp;&nbsp;&nbsp;- {$warn}<br>";
                }
            }
            return [
                'type'  => 'warning',
                'title' => 'Warning',
                'body'  => trim($formatted),
            ];
        }

        if (!empty($info)) {
            $formatted = "Please review the following information:<br><br>";
            $formatted .= "• Additional notes:<br>";
            foreach ($info as $inf) {
                $formatted .= "&nbsp;&nbsp;&nbsp;- {$inf}<br>";
            }
            return [
                'type'  => 'info',
                'title' => 'Info',
                'body'  => trim($formatted),
            ];
        }

        return [
            'type'  => 'success',
            'title' => 'Success',
            'body'  => 'Permissions updated successfully.',
        ];
    }
}
