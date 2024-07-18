<?php

// Declaring namespace
namespace LaswitchTech\coreAuth;

// Import additionnal class into the global namespace
use Exception;

// Import Sub-Namespaces class into the global namespace
use LaswitchTech\coreAuth\Types\Cookie;

class Compliance {

	// Auth Modules
    private $Cookie;

	// Auth Switches
    private $Submitted = false;

    /**
     * Create a new Offcanvas instance.
     *
     * @return void
     */
    public function __construct(){

        // Initialize Cookie
        $this->Cookie = new Cookie();

        // _REQUEST: { "cookiesAcceptEssentials": "on", "cookiesAcceptPerformance": "on", "cookiesAcceptQuality": "on", "cookiesAcceptPersonalisations": "on", "cookiesAccept": "" }
        if(isset($_REQUEST,$_REQUEST['cookiesAccept'])){

            // Set Cookie `cookiesAccept`
            $this->Cookie->set('cookiesAcceptEssentials', true, ['force' => true]);

            // Verify if consent was given for Performance Cookies
            if(isset($_REQUEST['cookiesAcceptPerformance']) && $_REQUEST['cookiesAcceptPerformance'] === "on"){

                // Set Cookie `cookiesAcceptPerformance`
                $this->Cookie->set('cookiesAcceptPerformance', true);
            }

            // Verify if consent was given for Quality Cookies
            if(isset($_REQUEST['cookiesAcceptQuality']) && $_REQUEST['cookiesAcceptQuality'] === "on"){

                // Set Cookie `cookiesAcceptQuality`
                $this->Cookie->set('cookiesAcceptQuality', true);
            }

            // Verify if consent was given for Personalisations Cookies
            if(isset($_REQUEST['cookiesAcceptPersonalisations']) && $_REQUEST['cookiesAcceptPersonalisations'] === "on"){

                // Set Cookie `cookiesAcceptPersonalisations`
                $this->Cookie->set('cookiesAcceptPersonalisations', true);
            }

            // Form Submitted
            $this->Submitted = true;
        }

        // If Cookies are set, update Submitted
        if(isset($_COOKIE) && isset($_COOKIE['cookiesAcceptEssentials'])){
            $this->Submitted = true;
        }
    }

    /**
     * Create a bootstrap offcanvas.
     *
     * @return string
     */
    public function form(){
        if(!$this->Submitted){
            return $this->bootstrap();
        }
    }

    /**
     * Create a bootstrap offcanvas.
     *
     * @return string
     */
    private function bootstrap(){

        // Initiate HTML
        $html = PHP_EOL;
        $html .= '<div class="offcanvas offcanvas-bottom h-auto user-select-none show" data-bs-backdrop="static" tabindex="-1" id="OffcanvasCookie" aria-labelledby="OffcanvasCookieLabel" aria-modal="true" role="dialog">' . PHP_EOL;
        $html .= '  <div class="offcanvas-header">' . PHP_EOL;
        $html .= '    <h5 class="offcanvas-title fs-2 fw-light" id="OffcanvasCookieLabel">' . PHP_EOL;
        $html .= '      <i class="bi-person-lock me-2"></i>Your choice on cookies' . PHP_EOL;
        $html .= '    </h5>' . PHP_EOL;
        $html .= '    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>' . PHP_EOL;
        $html .= '  </div>' . PHP_EOL;
        $html .= '  <div class="offcanvas-body d-flex justify-content-center flex-column p-0">' . PHP_EOL;
        $html .= '    <form class="overflow-auto p-3" method="post">' . PHP_EOL;
        $html .= '      <p>We use essential cookies to make sure our application works. We\'d also like to set optional cookies to improve the quality and performance of our application and enable personalised features. Change preferences by clicking Cookie Settings. Allow all cookies by clicking Accept.</p>' . PHP_EOL;
        $html .= '      <p>' . PHP_EOL;
        $html .= '        <a class="text-decoration-none" href="" data-bs-toggle="collapse" data-bs-target="#cookiesCollapseLearn" aria-expanded="false" aria-controls="cookiesCollapseLearn">Learn More</a>' . PHP_EOL;
        $html .= '      </p>' . PHP_EOL;
        $html .= '      <div class="collapse" id="cookiesCollapseLearn">' . PHP_EOL;
        $html .= '        <div class="card card-body my-3 px-4">' . PHP_EOL;
        $html .= '          <p><h4>What are Cookies?</h4></p>' . PHP_EOL;
        $html .= '          <p>Cookies are small pieces of data sent from a website and stored on a visitor\'s browser. They are typically used to keep track of settings you\'ve selected and actions taken on a site.</p><p>There are two types of cookies:</p>' . PHP_EOL;
        $html .= '          <ul>' . PHP_EOL;
        $html .= '            <li>Session (transient) cookies: These cookies are erased when you close your browser, and do not collect information from your computer. They typically store information in the form of a session identification that does not personally identify the user.</li>' . PHP_EOL;
        $html .= '            <li>Persistent (permanent or stored) cookies: These cookies are stored on your hard drive until they expire (at a set expiration date) or until you delete them. These cookies are used to collect identifying information about the user, such as web surfing behavior or user preferences for a specific site.</li>' . PHP_EOL;
        $html .= '          </ul>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= '      </div>' . PHP_EOL;
        $html .= '      <div class="collapse" id="cookiesCollapseSettings">' . PHP_EOL;
        $html .= '        <div class="card card-body my-3">' . PHP_EOL;
        $html .= '          <ul class="list-group">' . PHP_EOL;
        $html .= '            <li class="list-group-item">' . PHP_EOL;
        $html .= '              <div class="form-check form-switch mt-2">' . PHP_EOL;
        $html .= '                <input class="form-check-input" type="checkbox" role="switch" id="cookiesAcceptEssentials" name="cookiesAcceptEssentials" checked="checked" disabled="disabled">' . PHP_EOL;
        $html .= '                <label class="form-check-label" for="cookiesAcceptEssentials">Essentials</label>' . PHP_EOL;
        $html .= '              </div>' . PHP_EOL;
        $html .= '              <small>Required for the application to work</small>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;
        $html .= '            <li class="list-group-item">' . PHP_EOL;
        $html .= '              <div class="form-check form-switch mt-2">' . PHP_EOL;
        $html .= '                <input class="form-check-input" type="checkbox" role="switch" id="cookiesAcceptPerformance" name="cookiesAcceptPerformance" checked="checked">' . PHP_EOL;
        $html .= '                <label class="form-check-label" for="cookiesAcceptPerformance">Performance</label>' . PHP_EOL;
        $html .= '              </div>' . PHP_EOL;
        $html .= '              <small>Cached information use to improve the overall performance</small>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;
        $html .= '            <li class="list-group-item">' . PHP_EOL;
        $html .= '              <div class="form-check form-switch mt-2">' . PHP_EOL;
        $html .= '                <input class="form-check-input" type="checkbox" role="switch" id="cookiesAcceptQuality" name="cookiesAcceptQuality" checked="checked">' . PHP_EOL;
        $html .= '                <label class="form-check-label" for="cookiesAcceptQuality">Quality</label>' . PHP_EOL;
        $html .= '              </div>' . PHP_EOL;
        $html .= '              <small>Anonymous information use to improve the quality of the user experience</small>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;
        $html .= '            <li class="list-group-item">' . PHP_EOL;
        $html .= '              <div class="form-check form-switch mt-2">' . PHP_EOL;
        $html .= '                <input class="form-check-input" type="checkbox" role="switch" id="cookiesAcceptPersonalisations" name="cookiesAcceptPersonalisations" checked="checked">' . PHP_EOL;
        $html .= '                <label class="form-check-label" for="cookiesAcceptPersonalisations">Personalisations</label>' . PHP_EOL;
        $html .= '              </div>' . PHP_EOL;
        $html .= '              <small>Information use to personalise the user experience</small>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;
        $html .= '          </ul>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= '      </div>' . PHP_EOL;
        $html .= '      <div class="d-flex justify-content-around mx-auto my-4">' . PHP_EOL;
        $html .= '        <button class="btn btn-lg shadow btn-light" type="button" name="cookiesSettings" data-bs-toggle="collapse" data-bs-target="#cookiesCollapseSettings" aria-expanded="false" aria-controls="cookiesCollapseSettings">Cookie Settings</button>' . PHP_EOL;
        $html .= '        <button class="btn btn-lg shadow btn-primary" type="submit" name="cookiesAccept">Accept</button>' . PHP_EOL;
        $html .= '      </div>' . PHP_EOL;
        $html .= '    </form>' . PHP_EOL;
        $html .= '  </div>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        // Return
        return $html;
    }
}
