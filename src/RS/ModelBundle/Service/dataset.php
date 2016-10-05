<?php

namespace RS\ModelBundle\Service;

use Doctrine\ORM\EntityManager;

class dataset
{
    protected $em;
    protected $dataset;

    public function __construct(EntityManager $EntityManager)
    {
        $this->em = $EntityManager;
        $this->dataset = [
            '2048413/item_B4ALIDQMS3AHHUBOGJE5MNAGXO6GSQWJ',
            '2031901/I_101010_INT_1',
            '2023833/foto_epigrafi_immagini_uso_104_104009_1_jpg',
            '2048001/Athena_Plus_ProvidedCHO_KIK_IRPA__Brussels__Belgium__AP_10272754',
            '11620/MNHNBOTANY_MNHN_FRANCE_PC0498090',
            '15508/2674',
            '2022362/_Royal_Museums_Greenwich__http___collections_rmg_co_uk_collections_objects_36424',
            '9200405/BibliographicResource_3000117715294',
            '2026101/Partage_Plus_ProvidedCHO_Culture_Grid_PP0235_GM01',
            '2048001/Athena_Plus_ProvidedCHO_KIK_IRPA__Brussels__Belgium__AP_10367332',
            '2022365/Bristol_20Museums_2C_20Galleries_20_26_20Archives_emu_ecatalogue_ethnography_160531',
            '2026101/Partage_Plus_ProvidedCHO_Culture_Grid_PP0036_KS01',
            '2048011/work_72166',
            '9200134/BibliographicResource_2000000016004',
            '9200365/BibliographicResource_1000055157244',
            '2048011/work_81646',
            '2048100/MSS_Barb_gr_89',
            '2058811/DAI__7103d0d42aed6e55e5eedd07e1aef049__artifact__cho',
            '2048707/A_0_9_1447_Image',
            '2048005/Athena_Plus_ProvidedCHO_Nationalmuseum__Sweden__Inv__Nr__NMGu_2189___',
            '2048005/Athena_Plus_ProvidedCHO_Nationalmuseum__Sweden_9894',
            '2048005/Athena_Plus_ProvidedCHO_Nationalmuseum__Sweden_9913',
            '15501/at_imareal_017612',
            '08547/sgml_eu_php_obj_z0005150',
            '15508/34227',
            '9200440/BibliographicResource_3000127181291',
            '9200365/BibliographicResource_3000004794342',
            '2024914/photography_ProvidedCHO_Ajuntament_de_Girona_341275',
            '2058612/object_CHB_8733cde7c9180d3b4b6e22f6f4758d980b732475',
            '2051908/data_euscreenXL_ina_RXC00001252',
            '2023865/Objekt_DE_MUS_076017_lido_597',
            '2022608/TKM_TKM_1500_1978',
            '2064108/Museu_ProvidedCHO_M_nzkabinett__Staatliche_Museen_zu_Berlin_1853819',
            '2058811/DAI__c79f2e174bf4b5bd35c7cb93517dac47__artifact__cho',
            '91646/SMVK_MM_GreekRoman_3102409',
        ];
    }

    public function getDataSet()
    {
        return $this->dataset;
    }
}
