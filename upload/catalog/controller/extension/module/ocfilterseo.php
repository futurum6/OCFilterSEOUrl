<?php
class ControllerExtensionModuleOCFilterSeo extends Controller
{

    /**
     * Add SEO URLs to the filter values.
     *
     * @param array $filters The filters to which SEO URLs will be added.
     * @return array The filters with added SEO URLs.
     */
    public function addLincks(array $filters): array
    {
        $params = $this->ocfilter->seo->getParams();
        $activeFilters = $this->ocfilter->params->decode($params);
        $canApplySeoUrls =  $this->canApplySeoUrls($activeFilters);

        if ($canApplySeoUrls['status'] == false) {
            return $filters;
        }
        foreach ($filters as &$filter) {
            if (in_array($filter['filter_key'], $canApplySeoUrls['lockedBlocks'])) {
                continue;
            }
            foreach (['values', 'hidden_values'] as $key) {
                if (isset($filter[$key])) {

                    foreach ($filter[$key] as &$value) {
                        $value['seo_url'] = $this->generateSeoUrl($activeFilters, $filter['filter_key'], $value);
                    }
                }
            }
        }
        return $filters;
    }

    /**
     * Check if SEO URLs can be applied based on the active filters and settings.
     *
     * @param array $activeFilters The currently active filters.
     * @return array An array containing the status and locked blocks information.
     */
    private function canApplySeoUrls(array $activeFilters): array
    {
        $reset = [
            'status' => true,
            'lockedBlocks' => [],
        ];

        $status = $this->config->get('ocfilterseo_status') ?? false;
        $maxFiltersBlocks = $this->config->get('ocfilterseo_max_link_blocks') ?? 2;
        $maxLinkInBlock = $this->config->get('ocfilterseo_max_filters_per_block') ?? 1;

        if (!$status) {
            $reset['status'] = false;
            return $reset;
        }

        foreach ($activeFilters as $key => $filter) {

            if ($this->getFeatureType($key) === 2) {
                continue;
            }
            if (count($filter) >= $maxLinkInBlock) {
                $reset['lockedBlocks'][] = $key;
            }
        }

        if (count($reset['lockedBlocks']) >= $maxFiltersBlocks) {
            $reset['status'] = false;
        }

        return $reset;
    }

    /**
     * Extract the feature type from the filter key.
     *
     * @param string $filterKey The key of the filter.
     * @return int The feature type ID.
     */
    private function getFeatureType(string $filterKey): int
    {
        return (int)explode('.', $filterKey)[0];
    }

    /**
     * Generate the SEO URL for a filter value.
     *
     * @param array $activeFilters The currently active filters.
     * @param string $feature The feature key.
     * @param array $value The filter value.
     * @return string The generated SEO URL.
     */
    private function generateSeoUrl(array $activeFilters, string $feature, array $value): string
    {
        return $value['selected']
            ? $this->removeFilterFromUrl($activeFilters, $feature, $value['value_id'])
            : $this->addFilterToUrl($activeFilters, $feature, $value['value_id']);
    }

    /**
     * Remove a filter from the URL.
     *
     * @param array $activeFilters The currently active filters.
     * @param string $featureKey The feature key.
     * @param int $valueId The ID of the value to be removed.
     * @return string The updated URL without the specified filter.
     */
    private function removeFilterFromUrl(array $activeFilters, string $featureKey, int $valueId): string
    {
        if (isset($activeFilters[$featureKey])) {
            $valueKey = array_search($valueId, $activeFilters[$featureKey]);
            unset($activeFilters[$featureKey][$valueKey]);
        }
        $parameter = $this->ocfilter->params->encode($activeFilters);

        return $this->ocfilter->seo->link($parameter);
    }

    /**
     * Add a filter to the URL.
     *
     * @param array $activeFilters The currently active filters.
     * @param string $feature The feature key.
     * @param int $valueId The ID of the value to be added.
     * @return string The updated URL with the specified filter.
     */
    private function addFilterToUrl(array $activeFilters, string $feature, int $valueId): string
    {
        $activeFilters[$feature][] = $valueId;
        $parameter = $this->ocfilter->params->encode($activeFilters);
        return $this->ocfilter->seo->link($parameter);
    }
}
