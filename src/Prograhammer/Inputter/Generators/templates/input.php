<?php namespace db;

use Eloquent, EasyInput, DB;

class Grid implements EasyInputInterface{

	// Settings
	public $validateInputClass = "Filter";
	
	// Get an instance of this grid
	public static function make(EasyInput $easyInput)
	{
		return $easyInput->make($this);
	}	 

	public function getColumns($input = array(), $data = array())
	{
		// Return column array
		return array(	array("name"=>"id",	 "header"=>"ID",   "width"=>"50"),
					 	array("name"=>"name","header"=>"Name", "width"=>"100"),
					 	array("name"=>"city","header"=>"City", "width"=>"75")
					 );		
	}


	public function getRows($input = array(), $data = array())
	{
		
		// Handle any additional validation for $input
		

		// Handle any conditions for working with dynamic queries
		

		// Handle "Order By",  $input['sidx'] is sort column, $input['sord'] is "asc" or "desc"
				
	
		// Build "Count" query	$input['page'] is current page num, $input['perPage'] is limit	
		
		
		// Build Main query
		
		
		
		//******************************************************************************
		// from
		$query = DB::table('tad_schools AS schools');
		$count_query = DB::table('tad_schools AS schools');
		
		// sd
		$query->leftJoin('tad_sd AS sd','sd.id','=','schools.sd_id');
						
		// cities
		$query->leftJoin('tad_cities AS cities', function($join)
				    {
						$join->on('cities.id','=','schools.city_id');
					});
		
		// counties
		$query->leftJoin('tad_counties AS counties','counties.id','=','schools.county_id');
		
		// dept
		$query->leftJoin('tad_dept AS dept', function($join) 
					{
						$join->on('dept.school_id','=','schools.id');
						$join->on('dept.type_id','=',DB::raw('?'));
			    	})
			  ->setBindings(array_merge($query->getBindings(),array("4")));
								
		// dept people
		$query->leftJoin('tad_dept_people AS dept_people', function($join) 
					{
						$join->on('dept_people.dept_id','=','dept.id');
						$join->on('dept_people.personnel_type_id','=',DB::raw('?'));
			    	})
			  ->setBindings(array_merge($query->getBindings(),array("4")));
							
		// people
		$query = $query->leftJoin('tad_people AS people','people.id','=','dept_people.person_id');
		
		// school
		$query->where('schools.active','1');
		if(!empty($this->grid_settings['params']['cty']))
		{
			$query->where('schools.city_id',$this->grid_settings['params']['cty']);
			$count_query->where('schools.city_id',$this->grid_settings['params']['cty']);
		}
				
		// select
		$query = $query->select(  'schools.id AS schools__id',
								  'schools.apname AS schools__apname',
								  'sd.name AS sd__name',
								  'people.id AS people__id',
								  'people.first_name AS people__first_name',
								  'people.middle_name AS people__middle_name',
								  'people.last_name AS people__last_name',
								  'people.preferred_name AS people__preferred_name',
								  'cities.name AS cities__name',
								  'counties.name AS counties__name',
								  'schools.phone AS schools__phone',
								  'schools.fax AS schools__fax',
								  'schools.url AS schools__url',
								  'schools.active AS schools__active'
								);
		
								
		// Run queries
		$this->grid_settings['count'] = $count_query->count();
		$offset = EasyInput::compute_offset($this->grid_settings['page'], $this->grid_settings['count'], $this->grid_settings['per_page']);		
		
		$rows = $query->skip($offset)
					  ->take($this->grid_settings['per_page'])
					  ->get();			
		
		
		// ******************************************************************
		
		
		
		
		
		//Set count and rows properties and return object
		$rowsObj 	   = new stdClass;
		$rowObj->count = $result_from_count_query;
		$rowObj->rows  = $result_from_main_query;
					
		return $rowsObj;
	}
	
	
	public function getRowsFormatted($input = array(), $data = array())
	{
		// Initialize row format array and get row data
		$rowsFormated = array();
		$rowsObj = $this->getRows();
	
	
		// Interate through row data and format
		foreach($rowsObj->rows as $row){
			
			$rowFormatted[] = "The row " . $row['some_column_name_here'] . " is now formatted!"; 
			
		}
		
	
        // Return a formatted rows object
        $rowsFormattedObj       = new stdClass;
        $rowsFormattedObj->rows = $rowsFormatted;
        
        return $rowsFormattedObj;		
		
	}
	
	
	/*
	 
	public function getRowsPDF()
	{
		
	}
	  
    public function getRowsCSV()
	{
	
	} 
	  
	etc... 
	 
	   
    */
    
     
	public function getRowData()
	{
		// Build queries
		
		// from
		$query = DB::table('tad_schools AS schools');
		$count_query = DB::table('tad_schools AS schools');
		
		// sd
		$query->leftJoin('tad_sd AS sd','sd.id','=','schools.sd_id');
						
		// cities
		$query->leftJoin('tad_cities AS cities', function($join)
				    {
						$join->on('cities.id','=','schools.city_id');
					});
		
		// counties
		$query->leftJoin('tad_counties AS counties','counties.id','=','schools.county_id');
		
		// dept
		$query->leftJoin('tad_dept AS dept', function($join) 
					{
						$join->on('dept.school_id','=','schools.id');
						$join->on('dept.type_id','=',DB::raw('?'));
			    	})
			  ->setBindings(array_merge($query->getBindings(),array("4")));
								
		// dept people
		$query->leftJoin('tad_dept_people AS dept_people', function($join) 
					{
						$join->on('dept_people.dept_id','=','dept.id');
						$join->on('dept_people.personnel_type_id','=',DB::raw('?'));
			    	})
			  ->setBindings(array_merge($query->getBindings(),array("4")));
							
		// people
		$query = $query->leftJoin('tad_people AS people','people.id','=','dept_people.person_id');
		
		// school
		$query->where('schools.active','1');
		if(!empty($this->grid_settings['params']['cty']))
		{
			$query->where('schools.city_id',$this->grid_settings['params']['cty']);
			$count_query->where('schools.city_id',$this->grid_settings['params']['cty']);
		}
				
		// select
		$query = $query->select(  'schools.id AS schools__id',
								  'schools.apname AS schools__apname',
								  'sd.name AS sd__name',
								  'people.id AS people__id',
								  'people.first_name AS people__first_name',
								  'people.middle_name AS people__middle_name',
								  'people.last_name AS people__last_name',
								  'people.preferred_name AS people__preferred_name',
								  'cities.name AS cities__name',
								  'counties.name AS counties__name',
								  'schools.phone AS schools__phone',
								  'schools.fax AS schools__fax',
								  'schools.url AS schools__url',
								  'schools.active AS schools__active'
								);
		
								
		// Run queries
		$this->grid_settings['count'] = $count_query->count();
		$offset = EasyInput::compute_offset($this->grid_settings['page'], $this->grid_settings['count'], $this->grid_settings['per_page']);		
		
		$rows = $query->skip($offset)
					  ->take($this->grid_settings['per_page'])
					  ->get();		
		
		//if(!empty($this->grid_settings->params['cty']))
		//{
		//	$query = $query->where('schools.city',$this->grid_settings->params['cty']);
		//	$count_query = $count_query->where('schools.city_id',$this->grid_settings->params['cty']);
		//}
		
		//$queries = DB::getQueryLog();
		//$last_query = end($queries);
		//print_r($last_query);
		//die();
		
		// return results
		return $rows;
				
	}
	
	
	public function grid_rows()
	{
		
		$i = 0;
		$rows = array();
		foreach($this->grid_query() as $row){
			$rows[$i]['id'] = $i+1;
			$rows[$i]['cell'] = array($row->schools__apname);
			$i++;
		}

		return $rows;

		//do a loop and format it....returns array of rows
	}


}