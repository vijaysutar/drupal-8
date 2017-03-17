<?php

namespace Drupal\token_auth\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Crypt;

/**
 * Class AuthTokenForm.
 *
 * @package Drupal\token_auth\Form
 */
class AuthTokenForm extends EntityForm
{
    /**
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state)
    {
        $form = parent::form($form, $form_state);

        $auth_token = $this->entity;

        $form['label'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Label'),
            '#maxlength' => 255,
            '#default_value' => $auth_token->label(),
            '#description' => $this->t("Label for the Authentication Token."),
            '#required' => TRUE,
        );

        if (!$auth_token->isNew()) {
            $form['token'] = [
                '#markup' => $auth_token->token(),
            ];
        }

        $form['id'] = array(
            '#type' => 'machine_name',
            '#default_value' => $auth_token->id(),
            '#machine_name' => array(
                'exists' => '\Drupal\token_auth\Entity\AuthToken::load',
            ),
            '#disabled' => !$auth_token->isNew(),
        );

        $form['status'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Published?'),
            '#default_value' => $auth_token->status(),
            '#description' => $this->t("Is the Authentication Token published?"),
            '#required' => TRUE,
        ];


        $form['TokenAuthUser'] = [
            '#type' => 'number',
            '#title' => $this->t('User ID'),
            '#default_value' => $auth_token->TokenAuthUser(),
            '#description' => $this->t("User ID which token belongs."),
            '#required' => TRUE,
        ];

        /* You will need additional form elements for your custom properties. */

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $auth_token = $this->entity;
        if ($auth_token->isNew()) {
            $auth_token->set("token", Crypt::randomBytesBase64());
            $auth_token->set("created", time());
        }
        $status = $auth_token->save();

        switch ($status) {
            case SAVED_NEW:
                drupal_set_message($this->t('Created the %label Authentication Token.', [
                    '%label' => $auth_token->label(),
                ]));
                break;

            default:
                drupal_set_message($this->t('Saved the %label Authentication Token.', [
                    '%label' => $auth_token->label(),
                ]));
        }
        $form_state->setRedirectUrl($auth_token->urlInfo('collection'));
    }

}
