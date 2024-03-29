<?php

namespace ScottNason\EcoHelpers\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\DB;

class CheckEmails implements DataAwareRule, ValidationRule
{

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];       // Somehow automatically pulls in the request()->input() object.
    protected $user;            // Calling program passes the $user object we're validating here.
                                // ? But his worked on test vm without it. But JMP required it??

    protected $emails_to_check = [
        'email_personal',
        'email_alternate',
        //'email'   // this is really one of the other 2 (personal or alternate) - so don't check it.
    ];               // List of email fields to check.

    /**
     * Added a __construct method to allow passing in the current $user
     * @param $param
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }


    /**
     * Validation rule to determine if any of the email fields defined in $this->>emails_to_check
     * are in use in another email field for this user or if they are in use in any email field
     * for any other user in the system.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {


        if (empty($this->user['id'])) {
            // When adding a new record
            // There is no user id to check yet.

        } else {

            // Versus updating a current record.
            // We have a current user id to check.

        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Unique against this user's own 3 fields.
        // Check to see if this user is using this email in any of the other email fields.
        // Not sure, but is this already being handled by the default validation rules? (I don't think so?)
        //  (except the one that called this rule)
        $result = [];               // Make sure we don't try to count() a missing variable.
        foreach ($this->emails_to_check as $email) {
            // If this $mail is not the one that called this validation rule then check it.
            if ($email != $attribute) {
                if ($this->data[$email] == $value) {
                    $fail("You're already using this email address ({$this->user->getLabel($attribute)}).");
                }
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Check for unique against all other user's email fields. (except for this user's id)
        // If we add anymore emails, may want to think about constructing this query using the
        // $emails_to_check field defined at the top?
        $result = DB::select("SELECT * FROM users WHERE 
        (  email_alternate = ? 
        OR email_alternate = ?
        OR email_alternate = ?
        OR email_personal = ?
        OR email_personal = ?
        OR email_personal = ?
        OR email = ?
        OR email = ?
        OR email = ?
        )
        AND id <> ?
        ;",
        [
            $this->data['email_alternate'], $this->data['email_personal'], $this->data['email'],
            $this->data['email_alternate'], $this->data['email_personal'], $this->data['email'],
            $this->data['email_alternate'], $this->data['email_personal'], $this->data['email'],
            $this->user['id']
        ]
    );

        // If the query returns something then this rule fails.
        // Greater than zero (records returned) will fail the "unique across all users" validation check.
        if (count($result) > 0) {
            $fail("Someone else is already using this email address ({$this->user->getLabel($attribute)}).");
        }

    }


}
