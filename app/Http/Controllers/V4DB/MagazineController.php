<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\SearchQueryBuilderAnime;
use App\Http\QueryBuilder\SearchQueryBuilderMagazine;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\MagazineCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\NewsResource;
use App\Magazine;
use App\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jikan\Request\Anime\AnimeNewsRequest;
use Jikan\Request\Magazine\MagazineRequest;
use Jikan\Request\Magazine\MagazinesRequest;
use MongoDB\BSON\UTCDateTime;

class MagazineController extends Controller
{

    const MAX_RESULTS_PER_PAGE = 25;

    /**
     *  @OA\Get(
     *     path="/magazines",
     *     operationId="getMagazines",
     *     tags={"magazines"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Magazines Resource",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderMagazine::query(
            $request,
            Magazine::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new MagazineCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/magazines/{id}",
     *     operationId="getMagazineById",
     *     tags={"magazines"},
     * 
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Magazine's manga",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/magazine"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function resource(Request $request, int $id)
    {
        $page = $request->get('page') ?? 1;

        $results = Manga::query()
            ->where('serializations.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new MangaCollection(
            $results
        );
    }
}