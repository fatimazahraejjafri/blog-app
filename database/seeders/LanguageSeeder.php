<?php
namespace Database\Seeders;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $en = Language::create(['name' => 'English', 'code' => 'en', 'is_default' => true]);
        $fr = Language::create(['name' => 'Français', 'code' => 'fr']);

        $keys = [
    'app' => [
        'posts'             => ['Posts', 'Postes'],
        'post'              => ['Post', 'Poste'],
        'create_post'       => ['Create Post', 'Créer une poste'],
        'create_first_post' => ['Create Your First Post', 'Créer votre premier poste'],
        'create_post_desc'  => ['Fill in the details below to create a new blog post.', 'Remplissez les détails ci-dessous pour créer un nouvel article.'],
        'edit_post'         => ['Edit Post', 'Modifier le poste'],
        'edit_post_desc'    => ['Update the details of your blog post.', 'Mettez à jour les détails de votre poste.'],
        'search'            => ['Search', 'Rechercher'],
        'search_posts'      => ['Search posts...', 'Rechercher des postes...'],
        'status'            => ['Status', 'Statut'],
        'all_status'        => ['All Status', 'Tous les statuts'],
        'pending'           => ['Pending', 'En attente'],
        'draft'             => ['Draft', 'Brouillon'],
        'published'         => ['Published', 'Publié'],
        'archived'          => ['Archived', 'Archivé'],
        'category'          => ['Category', 'Catégorie'],
        'all_categories'    => ['All Categories', 'Toutes les catégories'],
        'author'            => ['Author', 'Auteur'],
        'date'              => ['Date', 'Date'],
        'actions'           => ['Actions', 'Actions'],
        'view'              => ['View', 'Voir'],
        'edit'              => ['Edit', 'Modifier'],
        'delete'            => ['Delete', 'Supprimer'],
        'delete_post'       => ['Delete Post', 'Supprimer l\'article'],
        'cancel'            => ['Cancel', 'Annuler'],
        'close'             => ['Close', 'Fermer'],
        'no_posts'          => ['No posts found.', 'Aucun article trouvé.'],
        'visibility'        => ['Visibility', 'Visibilité'],
        'content'           => ['Content', 'Contenu'],
    ],
];

        foreach ($keys as $group => $translations) {
            foreach ($translations as $key => [$enVal, $frVal]) {
                Translation::create(['language_id' => $en->id, 'group' => $group, 'key' => $key, 'value' => $enVal]);
                Translation::create(['language_id' => $fr->id, 'group' => $group, 'key' => $key, 'value' => $frVal]);
            }
        }
    }
}