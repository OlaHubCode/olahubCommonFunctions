<?php

namespace OlaHub\Middlewares;

use Closure;
use OlaHub\Models\Country;
use OlaHub\Models\Language;

class LocaleMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $defLang = FALSE;
        $country = Country::where('two_letter_iso_code', env('DEFAULT_COUNTRY_CODE', 'JO'))->first();
        $defCountry = $country->id;

        $countryCode = $request->headers->get('country');
        if ($countryCode && $countryCode > 0) {
            $country = Country::find($countryCode);
            if ($country) {
                $defCountry = $country->id;
            }
        }

        $languageCode = $request->headers->get('language'); //explode('_', $request->headers->get('language'))[0];
        if ($languageCode) {
            $language = Language::where('default_locale', $languageCode)->first();
            if ($language) {
                $defLang = $language->default_locale; //explode('_', $language->default_locale)[0];
            }
        }
        
        if (!$defLang) {
            $language = Language::find($country->language_id);
            if ($language) {
                $defLang = $language->default_locale; //explode('_', $language->default_locale)[0];
            }
        }

        config(['def_lang' => $defLang]);
        config(['def_country' => $defCountry]);
        return $next($request);
    }

}
