import { AbstractControl } from '@angular/forms';

/**
 * validates closure date for business jobs add page
 * @param control
 */
export function ValidateApplicationClosureDate(control: AbstractControl) {
    const currentDate = new Date();
    const lastDateBeforeClosure = new Date().setMonth(currentDate.getMonth() + 1);
    if (control.value) {
        if (control.value.jsdate) {
            const selectedDate = control.value.jsdate.getTime();
            if ((selectedDate > lastDateBeforeClosure) || (selectedDate < currentDate.getTime())) {
                return { validApplicationClosureDate: true };
            } else {
                return null;
            }
        }
    }
}


/**
 * validates closure date for business jobs add page
 * @param control
 */
export function ValidateNumber(control: AbstractControl) {
  if (!control.value) {
    return { validNumber: true };
  }else if (control.value.length < 12 ) {
    return { validNumber: true };
  } else {
    return null;
  }
}

/**
 * validates closure date for business edit page
 * @param jobCreatedDate
 */
export const closureDateValidator = (jobCreatedDate) => {
    return (control: AbstractControl) => {
        const currentDate = new Date();
        const jobCreatedDateObject = new Date(jobCreatedDate);
        const lastDateBeforeClosure = jobCreatedDateObject.setMonth(jobCreatedDateObject.getMonth() + 1);
        if (control.value) {
            if (control.value.jsdate) {
                const selectedDate = control.value.jsdate.getTime();
                if ((selectedDate > lastDateBeforeClosure) || (selectedDate < currentDate.getTime())) {
                    return { validApplicationClosureDate: true };
                } else {
                    return null;
                }
            }
        }
        return null;
    };
};

/**
 * validates availability date
 * @param control
 */
export function ValidateAvailabilityDate(control: AbstractControl) {
  const currentDate = new Date();
  if (control.value) {
    if (control.value.jsdate) {
      const selectedDate = control.value.jsdate.getTime();
      if (selectedDate < currentDate.getTime()) {
        return { validAvailabilityDate: true };
      } else {
        return null;
      }
    }
  }
  else if(control.value === ''){
    return { validAvailabilityDateEnter: true };
  }
}

/**
 * validates id number
 * @param control
 * @returns {{}}
 * @constructor
 */
export function ValidateIdNumber(control: AbstractControl) {
  const idNumber = control.value;
  const invalid = 0;
  const validationErrors = {};

  if(idNumber === null) {
    return null;
  } else if (idNumber.length > 0) {
    if (isNaN(idNumber)) {
      validationErrors['invalidIdNumber'] = 'Value supplied is not a valid number';
    }

    if (idNumber.length !== 13) {
      validationErrors['invalidIdLength'] = 'Number supplied does not have 13 digits.';
    }

    const yy = idNumber.substring(0, 2);
    const mm = idNumber.substring(2, 4);
    const dd = idNumber.substring(4, 6);

    const dob = new Date(yy, (mm - 1), dd);

    if (!(((dob.getFullYear() + '').substring(2, 4) === yy) && (dob.getMonth() === mm - 1) && (dob.getDate() == dd))) {
      validationErrors['invalidDateInLastSixDigits'] = 'Date in first 6 digits is invalid.';
    }

    if (idNumber.substring(10, 11) > 1) {
      validationErrors['invalidThirdToLast'] = `Third to last digit can only be a 0 or 1 but is a ${idNumber.substring(10, 11)}`;
    }

    if (idNumber.substring(11, 12) < 8) {
      validationErrors['invalidSecondToLastDigit'] = `Second to last digit can only be a 8 or 9 but is a ${idNumber.substring(11, 12)}`;
    }

    let ncheck = 0;
    let beven = false;

    for (let c = idNumber.length - 1; c >= 0; c--) {
      const cdigit = idNumber.charAt(c);
      let ndigit = parseInt(cdigit, 10);

      if (beven) {
        if ((ndigit *= 2) > 9) {
            ndigit -= 9;
        }
      }

      ncheck += ndigit;
      beven = !beven;
    }

    if ((ncheck % 10) !== 0) {
      validationErrors['invalidCheckbit'] = 'Checkbit is incorrect.';
    }
  }

  return (validationErrors) ? validationErrors : null;
}
