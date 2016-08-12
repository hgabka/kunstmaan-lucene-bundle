<?php

namespace Hgabka\LuceneBundle\Controller;

use Kunstmaan\AdminBundle\Controller\BaseSettingsController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SettingsController extends BaseSettingsController
{
    /**
     * @Route("/index", name="hgabka_lucene_bundle_settings_index")
     */
    public function indexAction()
    {
        $this->checkPermission();

        return $this->render('HgabkaLuceneBundle:Settings:index.html.twig');
    }

    /**
     * @Route("/regenerate", name="hgabka_lucene_bundle_settings_regenerate")
     */
    public function regenerateAction()
    {
        $this->checkPermission();

        $searchConfigurationChain = $this->get('kunstmaan_search.search_configuration_chain');

        try {
            foreach ($searchConfigurationChain->getConfigurations() as $alias => $searchConfiguration) {
                $searchConfiguration->deleteIndex();
                $searchConfiguration->createIndex();
                $searchConfiguration->populateIndex();
            }

            $this->addFlash('success', 'hgabka_lucene.success.regenerate_index');
        } catch (\Exception $e) {
            $this->addFlash('error', $this->get('translator')->trans('hgabka_lucene.error.regenerate_index', [
                '%error%' => $e->getMessage(),
            ]));
        }

        return $this->redirectToRoute('HgabkaLuceneBundle_settings_index');
    }
}
