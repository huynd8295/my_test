<?php
class Travel
{
    public function getTravels()
    {
        $curlTravelsSession = curl_init();
        $travelsUrl = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';
        curl_setopt($curlTravelsSession, CURLOPT_URL, $travelsUrl);
        curl_setopt($curlTravelsSession, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($curlTravelsSession), true);
    }
}
class Company
{
    public function getCompanies()
    {
        $curlCompaniesSession = curl_init();
        $companiesUrl = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';
        curl_setopt($curlCompaniesSession, CURLOPT_URL, $companiesUrl);
        curl_setopt($curlCompaniesSession, CURLOPT_RETURNTRANSFER, true);
        return json_decode(curl_exec($curlCompaniesSession), true);
    }

    public function buildCompanyTree($jsonTravelsData, array $elements, $parentId = "0")
    {
        $output = array();
        foreach ($elements as $element) {
            if ($element['parentId'] == $parentId) {
                unset($element['createdAt']);
                $element['cost'] = 0;
                $children = $this->buildCompanyTree($jsonTravelsData, $elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $travels = array_filter($jsonTravelsData, function($ar) use($element) {
                    return ($ar['companyId'] == $element['id']);
                });
                $prices = array_column($travels, 'price');
                array_walk_recursive($prices, function($item, $key) use(&$element) {
                    $element['cost'] += floatval($item);
                });
                $output[] = $element;
            }
        }
        return $output;
    }

    public function sumCosts(&$company)
    {
        unset($company['parentId']);
        $sum = $company['cost'];
        if (isset($company['children'])) {
            foreach($company['children'] as &$item){
                $sum += $this->sumCosts($item);
            }
        }
        $company['cost'] = $sum;
        return $sum;
    }
}
class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        $travel = new Travel();
        $company = new Company();
        $result = $company->buildCompanyTree($travel->getTravels(), $company->getCompanies());
        $company->sumCosts($result[0]);
        var_dump($result);
        echo 'Total time: '.  (microtime(true) - $start);
    }
}
(new TestScript())->execute();