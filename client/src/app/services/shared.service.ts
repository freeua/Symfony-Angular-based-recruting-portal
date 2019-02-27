import { Injectable, NgZone } from '@angular/core';
import { MapsAPILoader } from '@agm/core';
import {} from '@types/googlemaps';
import { FormArray, FormControl, FormGroup } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { Router } from '@angular/router';
import { AuthService } from './auth.service';
import { SettingsApiService } from './settings-api.service';
import { HttpClient } from '@angular/common/http';
import { AdminBadges, BusinessBadges, CandidateBadges } from '../../entities/models';

@Injectable()
export class SharedService extends SettingsApiService{

  constructor(
    protected readonly _http: HttpClient,
    protected readonly _authService: AuthService,
    protected readonly _router: Router,
    private readonly _mapsAPILoader: MapsAPILoader,
    private readonly _ngZone: NgZone,
    private readonly _toastr: ToastrService
  ) {
    super(_http, _authService, _router);
  }

  public visibleErrorVideoIcon = false;
  public visibleErrorProfileIcon = false;
  public preRouterLink: string;

  public componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    postal_code: 'short_name',
    sublocality_level_2: 'long_name',
  };

  public firstPopup: boolean;

  public progressBar: number;
  public checkSidebar = false;
  public preloaderView = true;

  public sidebarAdminBadges = new AdminBadges({});
  public sidebarCandidateBadges = new CandidateBadges({});
  public sidebarBusinessBadges = new BusinessBadges({});
  public checkStatusPopup = false;

  /**
   * Get Admin badges
   */
  public async getAdminBadges() {
    const headers = await this.createAuthorizationHeader();

    this._http.get<any>('/api/admin/badges', headers)
      .subscribe(data => {
        this.sidebarAdminBadges = new AdminBadges(data);
      })
  }

  /**
   * Get Candidate badges
   */
  public async getCandidateBadges() {
    const headers = await this.createAuthorizationHeader();

    this._http.get<any>('/api/candidate/badges', headers)
      .subscribe(data => {
        this.sidebarCandidateBadges = new CandidateBadges(data);
      })
  }

  /**
   * Get Business badges
   */
  public async getBusinessBadges() {
    const headers = await this.createAuthorizationHeader();

    this._http.get<any>('/api/business/badges', headers)
      .subscribe(data => {
        this.sidebarBusinessBadges = new BusinessBadges(data);
      })
  }

  /**
   * validates all form's fields
   * @param formGroup - group of controls
   * @returns void
   */
  public validateAllFormFields(formGroup: FormGroup): void {
    Object.keys(formGroup.controls).forEach((field) => {
      const control = formGroup.get(field);
      if (control instanceof FormControl) {
        control.markAsTouched({ onlySelf: true });
      } else if (control instanceof FormGroup) {
        this.validateAllFormFields(control);
      } else if (control instanceof FormArray) {
        if (control.controls.length > 0) {
          Object.keys(control.controls).forEach((index) => {
            Object.keys(control.controls[index]['controls']).forEach((groupControl) => {
              control.controls[index]['controls'][groupControl].markAsTouched({ onlySelf: true });
            });
          });
        }
      }
    });
  }

  /**
   * validates all form's fields
   * @param formGroup - group of controls
   * @returns void
   */
  public validateAllFormFieldsJob(formGroup): void {
    Object.keys(formGroup.controls).forEach((field) => {
      const control = formGroup.get(field);
      if (control instanceof FormControl) {
        control.markAsTouched({ onlySelf: true });
      } else if (control instanceof FormGroup) {
        this.validateAllFormFields(control);
      } else if (control instanceof FormArray) {
        if (control.controls.length > 0) {
          Object.keys(control.controls).forEach((index) => {
            Object.keys(control.controls[index]['controls']).forEach((groupControl) => {
              control.controls[index]['controls'][groupControl].markAsTouched({ onlySelf: true });
            });
          });
        }
      }
    });
  }

  /**
   * validates form from display alerts
   * @param form
   */
  public validateAlertCandidateForm(form): void{
    Object.keys(form.controls).forEach((field) => {
      const control = form.get(field);
      if (control instanceof FormControl) {
        if(control.invalid){
          switch (field) {
            case 'firstName':
              this._toastr.error('First Name is required');
              break;
            case 'homeAddress':
              this._toastr.error('Personal Address is required');
              break;
            case 'lastName':
              this._toastr.error('Last Name is required');
              break;
            case 'phone':
              this._toastr.error('Phone is required');
              break;
            case 'email':
              this._toastr.error('Email is required');
              break;
            case 'saicaStatus':
              this._toastr.error('Saica Status is required');
              break;
            case 'articlesFirm':
              this._toastr.error('Articles Firm is required');
              break;
            case 'saicaNumber':
              this._toastr.error('SAICA Number is required');
              break;
            case 'articlesFirmName':
              this._toastr.error('Articles Firm Name is required');
              break;
            case 'idNumber':
              this._toastr.error('ID Number is required');
              break;
            case 'boards':
              this._toastr.error('Boards is required');
              break;
            case 'dateAvailability':
              this._toastr.error('Date Availability is required');
              break;
            case 'citiesWorking':
              this._toastr.error('Cities working is required');
              break;
            case 'ethnicity':
              this._toastr.error('Ethnicity is required');
              break;
            case 'nationality':
              this._toastr.error('Nationality is required');
              break;
            case 'gender':
              this._toastr.error('Gender is required');
              break;
            case 'mostRole':
              this._toastr.error('Most Recent Role is required');
              break;
            case 'mostEmployer':
              this._toastr.error('Most Recent Employer is required');
              break;
            case 'dateArticlesCompleted':
              this._toastr.error('Date Articles Completed is required');
              break;
          }
        }
      }
    });
  }

  /**
   * validates form from display alerts
   * @param form
   */
  public validateJobForm(form): void{
    Object.keys(form.controls).forEach((field) => {
      const control = form.get(field);
      if (control instanceof FormControl) {
        if(control.invalid){
          switch (field) {
            case 'jobTitle':
              this._toastr.error('Job Title is required');
              break;
            case 'industry':
              this._toastr.error('Industry is required');
              break;
            case 'companyName':
              this._toastr.error('Company Name is required');
              break;
            case 'address':
              this._toastr.error('Address is required');
              break;
            case 'addressCountry':
              this._toastr.error('Address Country is required');
              break;
            case 'addressState':
              this._toastr.error('Address State is required');
              break;
            case 'addressZipCode':
              this._toastr.error('Postal Code is required');
              break;
            case 'addressCity':
              this._toastr.error('Address City is required');
              break;
            case 'addressSuburb':
              this._toastr.error('Address Suburb is required');
              break;
            case 'addressStreet':
              this._toastr.error('Address Street is required');
              break;
            case 'addressStreetNumber':
              this._toastr.error('Address Street Number is required');
              break;
            case 'companyDescription':
              this._toastr.error('Company Description is required');
              break;
            case 'roleDescription':
              this._toastr.error('Role Description is required');
              break;
            case 'closureDate':
              this._toastr.error('Closure Date is required');
              break;
          }
        }
      }
    });
  }

  /**
   * fetches address data from the google API
   * @returns void
   */
  public fetchGoogleAutocompleteDetails(form: FormGroup): void {
    const addressFieldComponent: HTMLElement = document.getElementById('search1');
    this._mapsAPILoader.load().then(() => {
      const autoComplete = new google.maps.places.Autocomplete((<HTMLInputElement>addressFieldComponent), { types: ['address'] });
      autoComplete.addListener('place_changed', () => {
        this._ngZone.run(() => {
          const place: google.maps.places.PlaceResult = autoComplete.getPlace();
          form.controls.address.setValue(place.formatted_address);
          form.controls['addressStreetNumber'].setValue('');
          form.controls['addressStreet'].setValue('');
          form.controls['addressSuburb'].setValue('');
          form.controls['addressCity'].setValue('');
          form.controls['addressState'].setValue('');
          form.controls['addressCountry'].setValue('');
          form.controls['addressZipCode'].setValue('');
          for (let i = 0; i < place.address_components.length; i++) {
            let addressType = place.address_components[i].types[0];
            if (addressType === 'sublocality_level_1') {
              addressType = 'sublocality_level_2';
            }
            if (this.componentForm[addressType]) {
              const valuePlace = place.address_components[i][this.componentForm[addressType]];
              (<HTMLInputElement>document.getElementById(addressType)).value = valuePlace;

              if (addressType === 'street_number') {
                form.controls['addressStreetNumber'].setValue(valuePlace);
              } else if (addressType === 'sublocality_level_2') {
                form.controls['addressSuburb'].setValue(valuePlace);
              } else if (addressType === 'route') {
                form.controls['addressStreet'].setValue(valuePlace);
              } else if (addressType === 'locality') {
                form.controls['addressCity'].setValue(valuePlace);
              } else if (addressType === 'administrative_area_level_1') {
                form.controls['addressState'].setValue(valuePlace);
              } else if (addressType === 'country') {
                form.controls['addressCountry'].setValue(valuePlace);
              } else if (addressType === 'postal_code') {
                form.controls['addressZipCode'].setValue(valuePlace);
              }
            }
          }
          if ( place.geometry === undefined || place.geometry === null ) {
            return;
          }
        });
      });
    });
  }

  /**
   * shows all errors performing requests
   * @param error
   */
  public showRequestErrors(error: any) {
    if (error.status === 403){
      window.location.reload();
    }
    else if (error.status === 401){
      this._authService.logout();
      window.location.reload();
    }
    else{
      if (error.error) {
        if (typeof error.error.error === 'string') {
          this._toastr.error(error.error.error);
        } else {
          error.error.error.forEach(data => {
            this._toastr.error(data);
          });
        }
      }
    }
  }

  /**
   * reset from
   * @param form
   * @returns void
   */
  public resetForm(form: FormGroup): void {
    form.reset();
  }

  /**
   * gets availability of business job in human readable form
   * @param availability {number} - integer representation of job availability {0, 1, 2 or 3}
   * @returns {string}
   */
  public getAvailabilityInHumanReadableForm(availability: number): string {
    const availabilityPossibleValues = {
      0: 'All',
      1: 'Immediately',
      2: 'Within 1 month',
      3: 'Within 3 month',
    };
    return availabilityPossibleValues[availability];
  }

  /**
   * gets nationality in human readable form
   * @param nationality {number} - integer representation of nationality {0, 1 or 2}
   * @returns {string}
   */
  public getNationalityInHumanReadableForm(nationality: number): string {
    const nationalityPossibleValues = {
      0: 'All',
      1: 'South African Citizen (BBBEE)',
      2: 'South African Citizen (Non-BBBEE)',
      3: 'Non-South African (With Permit)',
      4: 'Non-South African (Without Permit)'
    };
    return nationalityPossibleValues[nationality];
  }

  /**
   * gets qualification in human readable form
   * @param qualification {number} - integer representation of qualification {0, 1 or 2}
   * @returns {string}
   */
  public getQualificationInHumanReadableForm(qualification: number): string {
    const qualificationPossibleValues = {
      0: 'Newly qualified CA',
      1: 'Newly qualified CA',
      2: 'Part qualified CA',
    };
    return qualificationPossibleValues[qualification];
  }

  /**
   * gets boards in human readable form
   * @param boards - integer representation of qualification {1, 2, 3 or 4}
   * @returns {any}
   */
  public getBoardsInHumanReadableForm(boards: number): string {
    const boardsPossibleValues = {
      1: 'Passed Both Board Exams First Time',
      2: 'Passed Both Board Exams',
      3: 'ITC passed, APC Outstanding',
      4: 'ITC Outstanding',
    };
    return boardsPossibleValues[boards];
  }

  /**
   * gets availability in human readable form
   * @param availability {number} - integer representation of availability true - immediate, false - date of availability
   * @param availabilityPeriod {number}
   * @param dateAvailability {string} - data when candidate is available
   * @returns {any}
   */
  public getCandidateAvailabilityInHumanReadableForm(availability: boolean, availabilityPeriod: number, dateAvailability: string): string {
    const thingToShow = (new Date(dateAvailability).getTime() <  new Date().getTime())
        ? 'Immediate'
        : new Date(dateAvailability).toDateString();
    let returnDate;
    if (availability) {
      returnDate = 'Immediate';
    }
    else if(availabilityPeriod === null) {
      returnDate = '-';
    }
    else if(availabilityPeriod !== 4){
      if(availabilityPeriod === 1){
        returnDate = '30 Day notice period';
      }
      else if(availabilityPeriod === 2){
        returnDate = '60 Day notice period';
      }
      else if(availabilityPeriod === 3){
        returnDate = '90 Day notice period';
      }
    }
    else if(dateAvailability === null){
      returnDate = '-';
    }
    else{
      returnDate = thingToShow;
    }
    return returnDate;
  }

  /**
   * gets difference in days between two dates
   * @param date1
   * @param date2
   * @returns {number}
   */
  public getDifferenceInDays(date1, date2): number {
    const endDate = new Date(date2);
    const startDate = new Date(date1);
    const diffDays = Math.floor((endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24));
    return diffDays;
  }
}
