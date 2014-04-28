<?php

use Tester\Assert;

require __DIR__ . '/../../bootstrap-core.php';
require __DIR__ . '/../../../app/presenters/helpers/Helpers.php';

require_once __dir__ . "/../../../vendor/jiriknesl/mockista/bootstrap.php";


/**
 * PromotionClass
 */
test(function() {
	$inst = new App\Helpers(Null);
	Assert::null($inst->promotionClass(0));
	Assert::null($inst->promotionClass(1));
	Assert::same('promo-medium', $inst->promotionClass(2));
	Assert::same('promo-max', $inst->promotionClass(3));
	Assert::null($inst->promotionClass(4));
});



/**
 * mediaType
 */
test(function() {
	$inst = new App\Helpers(Null);
	Assert::same('file-avi', $inst->mediaType(0));
	Assert::same('file-avi', $inst->mediaType('video'));
	Assert::same('file-mp3', $inst->mediaType('music'));
	Assert::same('file-img', $inst->mediaType('image'));
	Assert::same('file-doc', $inst->mediaType('other'));
	Assert::same('file-doc', $inst->mediaType(''));
});



/**
 * daysLeft
 */
test(function() {
	$inst = new App\Helpers(Null);
	Assert::same('', $inst->daysLeft(Null));
	$d = new \DateTime();
	Assert::same('0 minutes left', $inst->daysLeft($d));
	$d->add(new DateInterval('PT10M'));
	Assert::same('10 minutes left', $inst->daysLeft($d));
	$d->add(new DateInterval('PT10H'));
	Assert::same('10 hours left', $inst->daysLeft($d));
	$d->add(new DateInterval('PT10H30S'));
	Assert::same('20 hours left', $inst->daysLeft($d));
	$d->add(new DateInterval('P10D'));
	Assert::same('10 days left', $inst->daysLeft($d));
});



/**
 * daysAgo
 */
test(function() {
	$mock = new Mockista\mock();
	$mock->translate('helpers.daysAgo.seconds', 10)->andReturn('%{val} seconds ago');
	$mock->translate('helpers.daysAgo.seconds', 14)->andReturn('%{val} seconds ago');
	$mock->translate('helpers.daysAgo.minutes', 4)->andReturn('%{val} minutes ago');
	$mock->translate('helpers.daysAgo.hours', 8)->andReturn('%{val} hours ago');
	$mock->translate('helpers.daysAgo.days', 10)->andReturn('%{val} days ago');
	$mock->translate('helpers.daysAgo.months', 9)->andReturn('%{val} months ago');
	$mock->translate('helpers.daysAgo.years', 4)->andReturn('%{val} years ago');
	$mock->freeze();

	$inst = new App\Helpers($mock);
	Assert::null($inst->daysAgo(Null));
	$d = new \DateTime();
	$d->sub(new DateInterval('PT10S'));
	Assert::same('10 seconds ago', $inst->daysAgo($d));
	$d->sub(new DateInterval('PT4S'));
	Assert::same('14 seconds ago', $inst->daysAgo($d));
	$d->sub(new DateInterval('PT4M'));
	Assert::same('4 minutes ago', $inst->daysAgo($d));
	$d->sub(new DateInterval('PT8H'));
	Assert::same('8 hours ago', $inst->daysAgo($d));
	$d->sub(new DateInterval('P10DT8H'));
	Assert::same('10 days ago', $inst->daysAgo($d));
	$d->sub(new DateInterval('P9MT8H'));
	Assert::same('9 months ago', $inst->daysAgo($d));
	$d->sub(new DateInterval('P4Y11DT8H'));
	Assert::same('4 years ago', $inst->daysAgo($d));
});



/**
 * resultClass
 */
test(function() {
	$inst = new App\Helpers(Null);
	Assert::same('accepted', $inst->resultClass(0));
	Assert::same('accepted', $inst->resultClass(1));
	Assert::same('pending', $inst->resultClass(2));
	Assert::same('satisfied', $inst->resultClass(3));
	Assert::same('not-satisfied', $inst->resultClass(4));
	Assert::same('accepted', $inst->resultClass(5));
	Assert::same('accepted', $inst->resultClass(6));
});
