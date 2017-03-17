<?php

namespace Drupal\token_auth;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Authentication Token entities.
 */
class AuthTokenListBuilder extends ConfigEntityListBuilder
{
    /**
     * {@inheritdoc}
     */
    public function buildHeader()
    {
        $header['label'] = $this->t('Authentication Token');
        $header['TokenAuthUser'] = $this->t('User ID');
        $header['token'] = $this->t('Token');
        $header['id'] = $this->t('Machine name');
        $header['status'] = $this->t('Enabled');
        $header['created'] = $this->t('Created');
        return $header + parent::buildHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['label'] = $entity->label();
        $row['TokenAuthUser'] = $entity->TokenAuthUser();
        $row['token'] = $entity->token();
        $row['id'] = $entity->id();
        if ($entity->status()) {
            $row['status'] = "Yes";
        } else {
            $row['status'] = "No";
        }
        $row['created'] = date('d-m-Y H:i',$entity->created());
        // You probably want a few more properties here...
        return $row + parent::buildRow($entity);
    }

}
