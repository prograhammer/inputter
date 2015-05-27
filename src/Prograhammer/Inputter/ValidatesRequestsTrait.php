<?php namespace Prograhammer\Inputter;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Foundation\Validation;

trait ValidatesRequestsTrait {
    /**
     * Validate the inputs with the given rules.
     *
     * @return void
     */
    public function validate()
    {
        $input = $this->toArrayUseAlias();
        $rules = $this->rulesToArray();
        $messages = $this->messagesToArray();

        $validator = $this->getValidationFactory()->make($input, $rules, $messages);

        if ($validator->fails() && $this->request->isMethod('post'))
        {
            $this->throwValidationException($this->request, $validator);
        }
    }

    /**
     * Throw the failed validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function throwValidationException(Request $request, $validator)
    {
        throw new HttpResponseException($this->buildFailedValidationResponse(
            $request, $this->formatValidationErrors($validator)
        ));
    }

    /**
     * Create the response for when a request fails validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $errors
     * @return \Illuminate\Http\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if ($request->ajax())
        {
            return new JsonResponse($errors, 422);
        }

        return redirect()->to($this->getRedirectUrl())
            ->withInput($request->input())
            ->withErrors($errors, $this->errorBag());
    }

    /**
     * Format the validation errors to be returned.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return array
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->getMessages();
    }

    /**
     * Get the URL we should redirect to.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        return app('Illuminate\Routing\UrlGenerator')->previous();
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app('Illuminate\Contracts\Validation\Factory');
    }

    /**
     * Get the key to be used for the view error bag.
     *
     * @return string
     */
    protected function errorBag()
    {
        return 'default';
    }

}

