<?php

namespace Frontend\AdminBundle\Controller\Subjects;

use Admingenerated\FrontendAdminBundle\BaseSubjectsController\ListController as BaseListController;

/**
 * ListController
 */
class ListController extends BaseListController
{
    protected function getQuery(){
        $query = $this->buildQuery();

        $this->processQuery($query);
        $this->processSort($query);
        $this->processFilters($query);
        $this->processScopes($query);

        // hozzájoinolni a tantárgyakat
        $query = $query->select('q, u, us')
                 ->leftJoin('q.user', 'u')
                 ->leftJoin('u.userSettings', 'us');
        
        
        return $query->getQuery();
    }
}
