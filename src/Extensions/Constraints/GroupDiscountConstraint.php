<?php

namespace SilverShop\Discounts\Extensions\Constraints;


use SilverShop\Discounts\Model\Discount;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Security\Group;
use SilverStripe\ORM\DataList;


class GroupDiscountConstraint extends DiscountConstraint
{
    private static $has_one = [
        "Group" => Group::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab("Root.Constraints.ConstraintsTabs.Membership",
            DropdownField::create("GroupID",
                _t(__CLASS__.'.MEMBERISINGROUP', "Member is in group"),
                Group::get()->map('ID', 'Title')
            )->setHasEmptyDefault(true)
            ->setEmptyString(_t(__CLASS__.'.ANYORNOGROUP', 'Any or no group'))
        );
    }

    public function filter(DataList $list)
    {
        $groupids = [0];
        if ($member = $this->getMember()) {
            $groupids = $groupids + $member->Groups()
                                        ->map('ID', 'ID')
                                        ->toArray();
        }

        return $list->filter("GroupID", $groupids);
    }

    public function check(Discount $discount)
    {
        $group = $discount->Group();
        $member = $this->getMember();
        if ($group->exists() && (!$member || !$member->inGroup($group))) {
            $this->error(_t(
                "Discount.GROUPED",
                "Only specific members can use this discount."
            ));
            return false;
        }

        return true;
    }

    public function getMember()
    {
        return isset($this->context['Member']) ? $this->context['Member'] : $this->order->Member();
    }
}
