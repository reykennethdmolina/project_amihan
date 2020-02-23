<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\RegistryForm;
use App\Models\RegistryFormAid;
use App\Models\RegistryFormAidItem;
use App\Models\RegistryFormFamily;
use App\Models\RegistryFormCrop;
use App\Models\RegistryFormFishery;
use App\Models\RegistryFormLivestock;
use Image;

class RegistryFormController extends Controller
{
    const farm_worker = '';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        session_start();
        $this->farm_worker = @$_SESSION['farm_worker'];

        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RegistryForm::where(['mao_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = ['salutation' => 'required|max:191',
                        'firstname' => 'required|max:191',
                        'middlename' => 'required|max:191',
                        'lastname' => 'required|max:191',
                        'hoblst' => 'required|max:191',
                        'barangay' => 'required|max:191',
                        'city' => 'required|max:191',
                        'province' => 'required|max:191',
                        'mobile' => 'required|min:11|max:11',
                        'gender' => 'required|max:191',
                        'civil_status' => 'required|max:191',
                        'birth_date' => 'required|max:191',
                        'religion' => 'required',
                        'education' => 'required|max:191',
                        'livelihood' => 'required|max:191',
                        'livelihood_type' => 'required|max:191',
                        'id_type' => 'required|max:191',
                        'id_no' => 'required|max:191',
                        'farm_name' => 'required|max:191',
                        'farm_lot' => 'required|max:191',
                        'farm_type' => 'required|max:191',
                        'farming_since' => 'required|min:4|max:4',
                        'form_type' => 'required|max:191'];

        if ($request['member_org'] == 'yes') { 
            $validation['name_of_org'] = 'required|max:191';
            $name_of_org = $request['name_of_org'];
        } else {
            $name_of_org = '';
        }

        $this->validate($request, $validation);
        
        if ($request['id_photo']) {
            $photo = $this->uploadPhoto($request['id_photo'], '', 1);
        } else {
            $photo = 'blank-profile-picture.png';
        }

        $form = RegistryForm::create([
            'mao_id' => Auth::id(),
            'salutation' => $request['salutation'],
            'firstname' => $request['firstname'],
            'middlename' => $request['middlename'],
            'lastname' => $request['lastname'],
            'suffix' => $request['suffix'],
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],
            'city' => $request['city'],
            'province' => $request['province'],
            'mobile' => $request['mobile'],
            'gender' => $request['gender'],
            'civil_status' => $request['civil_status'],
            'birth_date' => $request['birth_date'],
            'religion' => $request['religion'],
            'education' => $request['education'],
            'livelihood_type' => $request['livelihood_type'],
            'livelihood' => $request['livelihood'],
            'id_type' => $request['id_type'],
            'id_no' => $request['id_no'],
            'id_photo' => $photo,
            'farm_name' => $request['farm_name'],
            'farm_lot' => $request['farm_lot'],
            'farm_type' => $request['farm_type'],
            'farming_since' => $request['farming_since'],
            'form_type' => $request['form_type'],
            'member_org' => $request['member_org'],
            'name_of_org' => $name_of_org,
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return $form;
    }

    private function uploadPhoto($file, $currentPhoto, $counter) {
        if ($file != $currentPhoto) {
            $photo = auth('api')->user()->id.''.$counter.''.time().'.'.explode('/', explode(':', 
                    substr($file, 0, strpos($file, ';')))[1])[1];
            
            $img = Image::make($file);
            $img->save(public_path('img/id/').$photo);

            return $photo;
        } else {
            return $file;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])
                            ->with(['registryFormAid.items', 'family', 'crop', 'fishery', 'livestock'])
                            ->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();

        $validation = ['salutation' => 'required|max:191',
                        'firstname' => 'required|max:191',
                        'middlename' => 'required|max:191',
                        'lastname' => 'required|max:191',
                        'hoblst' => 'required|max:191',
                        'barangay' => 'required|max:191',
                        'city' => 'required|max:191',
                        'province' => 'required|max:191',
                        'mobile' => 'required|min:11|max:11',
                        'gender' => 'required|max:191',
                        'civil_status' => 'required|max:191',
                        'birth_date' => 'required|max:191',
                        'religion' => 'required',
                        'education' => 'required|max:191',
                        'livelihood' => 'required|max:191',
                        'livelihood_type' => 'required|max:191',
                        'id_type' => 'required|max:191',
                        'id_no' => 'required|max:191',
                        'farm_name' => 'required|max:191',
                        'farm_lot' => 'required|max:191',
                        'farm_type' => 'required|max:191',
                        'farming_since' => 'required|min:4|max:4',
                        'form_type' => 'required|max:191'];

        if ($request['member_org'] == 'yes') { 
            $validation['name_of_org'] = 'required|max:191';
            $name_of_org = $request['name_of_org'];
        } else {
            $name_of_org = '';
        }

        $this->validate($request, $validation);
        
        if ($request['id_photo']) {
            $photo = $this->uploadPhoto($request['id_photo'], $registry->id_photo, 1);
        } else {
            $photo = 'blank-profile-picture.png';
        }

        $registry->update([
            'salutation' => $request['salutation'],
            'firstname' => $request['firstname'],
            'middlename' => $request['middlename'],
            'lastname' => $request['lastname'],
            'suffix' => $request['suffix'],
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],
            'city' => $request['city'],
            'province' => $request['province'],
            'mobile' => $request['mobile'],
            'gender' => $request['gender'],
            'civil_status' => $request['civil_status'],
            'birth_date' => $request['birth_date'],
            'religion' => $request['religion'],
            'education' => $request['education'],
            'livelihood_type' => $request['livelihood_type'],
            'livelihood' => $request['livelihood'],
            'id_type' => $request['id_type'],
            'id_no' => $request['id_no'],
            'id_photo' => $photo,
            'farm_name' => $request['farm_name'],
            'farm_lot' => $request['farm_lot'],
            'farm_type' => $request['farm_type'],
            'farming_since' => $request['farming_since'],
            'form_type' => $request['form_type'],
            'member_org' => $request['member_org'],
            'name_of_org' => $name_of_org,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Registry updated'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function search()
    {   
        if ($search = \Request::get('q')) {
            $registry = RegistryForm::where(function($query) use ($search){
                $query->where('firstname', 'LIKE', "%$search%")
                    ->orWhere('middlename', 'LIKE', "%$search%")
                    ->orWhere('lastname', 'LIKE', "%$search%");
            })->where(['mao_id' => Auth::id()])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            return RegistryForm::where(['mao_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $registry;
    }

    public function storeAid(Request $request, $id) 
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();

        if ($registry) {

            $this->validate($request, [
                'program_name' => 'required|max:191',
                'source_of_funding' => 'required|max:191',
                'grants' => 'required|max:191',
            ]);
    
            $aid = RegistryFormAid::create([
                'mao_id' => Auth::id(),
                'registry_form_id' => $registry->id,
                'program_name' => $request['program_name'],
                'source_of_funding' => $request['source_of_funding'],
                'grants' => $request['grants'],
                'created_by' => $this->farm_worker,
                'updated_by' => $this->farm_worker,
            ]);
    
            $items = $request['aidItems'];

            if (!empty($items)) {
                foreach ($items as $item) {
                    if ($item['id'] == 'new') {
                        RegistryFormAidItem::create([
                            'mao_id' => Auth::id(),
                            'registry_form_aid_id' => $aid->id,
                            'name' => $item['name'],
                            'qty' => $item['qty'],
                            'amount' => $item['amount'],
                            'remarks' => $item['remarks'],
                            'created_by' => $this->farm_worker,
                            'updated_by' => $this->farm_worker,
                        ]);
                    } 
                }
            }

            return $aid;

        }

        return ['message' => 'Error'];

    }

    public function updateAid(Request $request, $id) {
        $aid = RegistryFormAid::where(['id' => $id])->first();

        if ($aid) {

            $this->validate($request, [
                'program_name' => 'required|max:191',
                'source_of_funding' => 'required|max:191',
                'grants' => 'required|max:191',
            ]);    
           
            $aid->update([
                'program_name' => $request['program_name'],
                'source_of_funding' => $request['source_of_funding'],
                'grants' => $request['grants'],
                'updated_by' => $this->farm_worker,
            ]);
            

            $items = $request['aidItems'];

            if (!empty($items)) {
                foreach ($items as $item) {
                    if ($item['id'] == 'new') {
                        RegistryFormAidItem::create([
                            'mao_id' => Auth::id(),
                            'registry_form_aid_id' => $aid->id,
                            'name' => $item['name'],
                            'qty' => $item['qty'],
                            'amount' => $item['amount'],
                            'remarks' => $item['remarks'],
                            'created_by' => $this->farm_worker,
                            'updated_by' => $this->farm_worker,
                        ]);
                    } else if($item['modify_by'] == 'del') {
                        $aidItem = RegistryFormAidItem::findOrFail($item['id']);

                        if ($aidItem) {
                            $aidItem->delete();  
                        } 
                    } else {
                        $aidItem = RegistryFormAidItem::findOrFail($item['id']);
                        $aidItem->update([
                            'name' => $item['name'],
                            'qty' => $item['qty'],
                            'amount' => $item['amount'],
                            'remarks' => $item['remarks'],
                            'updated_by' => $this->farm_worker,
                        ]);
                    }
                }
            }
    
            return $aid;

        }

        return ['message' => 'Error'];
    }

    public function destroyAid($id)
    {

        $aid = RegistryFormAid::findOrFail($id);

        if ($aid) {

            $aid->items()->delete();
            $aid->delete();
            return ['message' => 'Crop deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function storeFamily(Request $request, $id) 
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();

        if ($registry) {

            $this->validate($request, [
                'name' => 'required|max:191',
                'relationship' => 'required|max:191',
                'birth_date' => 'required|max:191',
                'gender' => 'required|max:191',
                'education' => 'required|max:191',
            ]);
    
            $family = RegistryFormFamily::create([
                'mao_id' => Auth::id(),
                'registry_form_id' => $registry->id,
                'name' => $request['name'],
                'relationship' => $request['relationship'],
                'birth_date' => $request['birth_date'],
                'gender' => $request['gender'],
                'education' => $request['education'],
                'created_by' => $this->farm_worker,
                'updated_by' => $this->farm_worker,
            ]);
    
        
            return $family;

        }

        return ['message' => 'Error'];

    }

    public function updateFamily(Request $request, $id) {
        $family = RegistryFormFamily::where(['id' => $id])->first();

        if ($family) {

            $this->validate($request, [
                'name' => 'required|max:191',
                'relationship' => 'required|max:191',
                'birth_date' => 'required|max:191',
                'gender' => 'required|max:191',
                'education' => 'required|max:191',
            ]);

            $family->update([
                'name' => $request['name'],
                'relationship' => $request['relationship'],
                'birth_date' => $request['birth_date'],
                'gender' => $request['gender'],
                'education' => $request['education'],
                'updated_by' => $this->farm_worker,
            ]);
    
            return $family;

        }

        return ['message' => 'Error'];
    }

    public function destroyFamily($id)
    {

        $family = RegistryFormFamily::findOrFail($id);

        if ($family) {

            $family->delete();
            return ['message' => 'Family member deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function storeCrop(Request $request, $id) 
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();

        if ($registry) {

            $this->validate($request, [
                'crop_category_id' => 'required|max:191',
                'crop_list_id' => 'required|max:191',
                'area_planted' => 'required|max:191',
                'totalplant' => 'required|max:191',
                'location_parcel' => 'required|max:191',
                'stage_crop' => 'required|max:191',
                'landholding_status' => 'required|max:191',
                'land_class' => 'required|max:191',
                'land_character' => 'required|max:191',
            ]);
    
            $crop = RegistryFormCrop::create([
                'mao_id' => Auth::id(),
                'registry_form_id' => $registry->id,
                'crop_category_id' => $request['crop_category_id'],
                'crop_list_id' => $request['crop_list_id'],
                'crop_name' => $request['crop_name'],
                'area_planted' => $request['area_planted'],
                'totalplant' => $request['totalplant'],
                'location_parcel' => $request['location_parcel'],
                'stage_crop' => $request['stage_crop'],
                'landholding_status' => $request['landholding_status'],
                'land_class' => $request['land_class'],
                'land_character' => $request['land_character'],
                'created_by' => $this->farm_worker,
                'updated_by' => $this->farm_worker,
            ]);
    
        
            return $crop;

        }

        return ['message' => 'Error'];

    }

    public function updateCrop(Request $request, $id) {
        $crop = RegistryFormCrop::where(['id' => $id])->first();

        if ($crop) {

            $this->validate($request, [
                'crop_category_id' => 'required|max:191',
                'crop_list_id' => 'required|max:191',
                'area_planted' => 'required|max:191',
                'totalplant' => 'required|max:191',
                'location_parcel' => 'required|max:191',
                'stage_crop' => 'required|max:191',
                'landholding_status' => 'required|max:191',
                'land_class' => 'required|max:191',
                'land_character' => 'required|max:191',
            ]);

            $crop->update([
                'crop_category_id' => $request['crop_category_id'],
                'crop_list_id' => $request['crop_list_id'],
                'crop_name' => $request['crop_name'],
                'area_planted' => $request['area_planted'],
                'totalplant' => $request['totalplant'],
                'location_parcel' => $request['location_parcel'],
                'stage_crop' => $request['stage_crop'],
                'landholding_status' => $request['landholding_status'],
                'land_class' => $request['land_class'],
                'land_character' => $request['land_character'],
                'updated_by' => $this->farm_worker,
            ]);
    
            return $crop;

        }

        return ['message' => 'Error'];
    }

    public function destroyCrop($id)
    {

        $crop = RegistryFormCrop::findOrFail($id);

        if ($crop) {

            $crop->delete();
            return ['message' => 'Crop deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function updateCropInfo(Request $request, $id)
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();
        
        $registry->update([
            'total_agri_area' => $request['total_agri_area'],
            'area_devoted_crop' => $request['area_devoted_crop'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Registry updated'];
    }

    public function updateFisheryInfo(Request $request, $id)
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();

        if (!$request['cultured']) {
            $request['cultured'] = '';
        }

        RegistryFormFishery::updateOrCreate(
            ['mao_id' => Auth::id(), 'registry_form_id' => $id],
            [
                'type_of_fishing' => $request['type_of_fishing'],
                'years_of_fishing' => $request['years_of_fishing'],
                'fishing_ground' => $request['fishing_ground'],
                'fishing_gear' => $request['fishing_gear'],
                'fishing_trip_week' => $request['fishing_trip_week'],
                'ave_catch_week' => $request['ave_catch_week'],
                'area_devoted_aqua' => $request['area_devoted_aqua'],
                'type_of_aquaculture' => $request['type_of_aquaculture'],
                'water_environment' => $request['water_environment'],
                'cultured' => $request['cultured'],
                'created_by' => $this->farm_worker,
                'updated_by' => $this->farm_worker,
            ]
        );
        

        return ['message' => 'Registry updated'];
    }

    public function updateLivestockInfo(Request $request, $id)
    {
        $registry = RegistryForm::where(['mao_id' => Auth::id(), 'id' => $id])->first();

        $livestock = $request['livestock'];
        $name = $request['name'];

        if ($livestock) {
            $count = 0;
            $stack = array();
            $livestock_id = 0;
            foreach ($livestock as $stock) {
                $value = '';
                if (abs($stock['value']) != 0) {
                    $value = $stock['id'].';'.$stock['name'].';'.abs($stock['value']);
                    $stack[] = $value;
                    $livestock_id = $stock['livestock_id'];
                }
                $count += abs($stock['value']);
            }
            
            RegistryFormLivestock::updateOrCreate(
                ['mao_id' => Auth::id(), 'registry_form_id' => $id, 'livestock_id' => $livestock_id],
                [
                    'livestock_id' => $livestock_id,
                    'livestock_name' => $name,
                    'count' => $count,
                    'items' => json_encode($stack),
                    'created_by' => $this->farm_worker,
                    'updated_by' => $this->farm_worker,
                ]
            );
        }
    }

    public function destroyLivestock($id)
    {

        $livestock = RegistryFormLivestock::findOrFail($id);

        if ($livestock) {

            $livestock->delete();
            return ['message' => 'Livestock member deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }
}
