<?php

namespace OlaHub\Helpers;

use Stichoza\GoogleTranslate\TranslateClient;

abstract class OlaHubCommonHelper {

    static function timeStampToDate($time, $format = 'D d F, Y') {
        $return = $time;
        if ($time && $time > 0) {
            $return = date($format, $time);
        } else {
            $return = "N/A";
        }
        return $return;
    }

    static function convertStringToDate($string, $format = 'D d F, Y') {
        $return = $string;
        if ($string) {
            $time = strtotime($string);
            if ($time && $time > 0) {
                $return = date($format, $time);
            } else {
                $return = "N/A";
            }
        }
        return $return;
    }

    static function createSlugFromString($string, $delimiter = '-') {
        $return = $string;
        if ($string) {
            $return = str_replace(' ', '_', $string);
            $return = preg_replace("/[^|+-_a-zA-Z0-9\/]/", '', $return);
            $return = strtolower(trim($return, '-'));
            $return = preg_replace("/[\/_|+ -]+/", $delimiter, $return);
        }

        return $return;
    }

    static function returnCurrentLangField($objectData, $fieldName) {
        $return = "N/A";
        $language = config('def_lang');
        if (isset($objectData->$fieldName)) {
            $jsonData = json_decode($objectData->$fieldName);
            if (isset($jsonData->$language) && !empty($jsonData->$language)) {
                $return = $jsonData->$language;
            } else {
                $return = $objectData->$fieldName;
            }
        }
        return $return;
    }

    static function defineRowCreator($data, $creatorColumn = 'created_by') {
        return isset($data->$creatorColumn) && $data->$creatorColumn > 0 ? $data->$creatorColumn : "N/A";
    }

    static function defineRowUpdater($data, $updaterColumn = 'updated_by') {
        return isset($data->$updaterColumn) && $data->$updaterColumn > 0 ? $data->$updaterColumn : "N/A";
    }

    public static function randomString($length = 8, $type = false) {
        switch ($type) {
            case 'str':
                $seed = str_split('abdefghijkmnqrtyABDEFGHJKLMNQRTY');
                break;
            case 'str_num':
                $seed = str_split('abdefghijkmnqrtyABDEFGHJKLMNQRTY123456789');
                break;
            case 'num':
                $seed = str_split('1234567890');
                break;
            case 'spc':
                $seed = str_split('!@$%^&*');
                break;
            case 'num_spc':
                $seed = str_split('1234567890!@$%^&*');
                break;
            case 'str_spc':
                $seed = str_split('abdefghijkmnqrtyABDEFGHJKLMNQRTY!@$%^&*');
                break;
            default :
                $seed = str_split('abdefghijkmnqrtyABDEFGHJKLMNQRTY123456789!@$%^&*');
                break;
        }

        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, $length) as $k)
            $rand .= $seed[$k];
        return $rand;
    }

    static function translate($string) {
        $languages = \OlaHub\Models\Language::all();
        $return = [];
        $tr = new TranslateClient(null, 'ar');
        $tr->translate($string);
        $return[$tr->getLastDetectedSource()] = $string;
        foreach ($languages as $one) {
            $language = explode('_', $one->default_locale);
            $languageCode = isset($language[0]) ? $language[0] : $language;
            if (!array_key_exists($one->default_locale, $return)) {
                $tr = new TranslateClient();
                $return[$one->default_locale] = $tr->setTarget($languageCode)->translate($string);
            }
        }
        return json_encode($return);
    }

    static function setImageUrl($imageID, $type = "image") {
        $return = null;
        if (STORAGE_URL) {
            $defult_url = STORAGE_URL;
        } else {
            $defult_url = url()."/temp_photos/";
        }
        if (strlen($imageID) > 4) {
            $imageID = str_replace("files", "files/", $imageID);
            $imageID = str_replace("temp_photos/", "", $imageID);
            if (strpos($imageID, "http") !== false || strpos($imageID, "https") !== false) {
                return $imageID;
            }
            $explodedData = explode('.', $imageID);
            $extension = end($explodedData);
            if (in_array(strtolower($extension), IMAGE_EXT)) {
                $return = $defult_url . "/$imageID";
            }
        }

        return $return;
    }

    static function setDefLang($country) {
        $countryCode = $country;
        if ($countryCode && $countryCode > 0) {
            $country = \OlaHub\Models\Country::find($countryCode);
            if ($country) {
                $defCountry = $country->id;
            }
        }

        $language = \OlaHub\Models\Language::find($country->language_id);
        if ($language) {
            $defLang = $language->default_locale; //explode('_', $language->default_locale)[0];
        }
        config(['def_lang' => $defLang]);
    }

    static function getDefineConst($constName, $constVal = 'false') {
        if (defined($constName)) {
            if ($constVal !== 'false') {
                runkit_constant_redefine($constName, $constVal);
            }
        } else {
            define($constName, $constVal);
        }
        return constant($constName);
    }

}
